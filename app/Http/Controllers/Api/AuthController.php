<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Wallet;
use App\Services\FirestoreService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;

class AuthController extends Controller
{
    /** @var FirestoreService */
    private $fs;

    public function __construct(FirestoreService $fs)
    {
        $this->fs = $fs;
    }

    /** Sync user data to Firestore users/{id} */
    private function syncUserToFirestore(User $user): void
    {
        $wallet = $user->wallet;
        $data = [
            'id'                  => (string) $user->id,
            'name'                => $user->name,
            'phone'               => $user->phone,
            'city'                => $user->city ?? '',
            'role'                => $user->role,
            'status'              => $user->status,
            'phone_verified'      => (bool) $user->phone_verified,
            'fcm_token'           => $user->fcm_token ?? null,
            'avatar'              => $user->avatar
                                    ? asset('storage/' . $user->avatar)
                                    : null,
            'wallet_balance'      => (int) ($wallet ? $wallet->balance : 0),
            'wallet_total_earned' => (int) ($wallet ? $wallet->total_earned : 0),
            'created_at'          => $user->created_at ? $user->created_at->toIso8601String() : '',
        ];
        $this->fs->set('users', (string) $user->id, $data);
    }

    /**
     * Register a new reseller account
     */
    public function register(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'name'     => 'required|string|max:100',
            'phone'    => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:6',
            'city'     => 'required|string|max:50',
        ], [
            'name.required'     => 'الاسم مطلوب.',
            'name.max'          => 'الاسم طويل جداً.',
            'phone.required'    => 'رقم الهاتف مطلوب.',
            'phone.unique'      => 'رقم الهاتف مسجل مسبقاً.',
            'password.required' => 'كلمة المرور مطلوبة.',
            'password.min'      => 'كلمة المرور يجب أن تكون 6 أحرف على الأقل.',
            'city.required'     => 'المدينة مطلوبة.',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name'     => $request->name,
            'phone'    => $request->phone,
            'password' => Hash::make($request->password),
            'city'     => $request->city,
            'role'     => 'reseller',
            'status'   => 'pending',
        ]);

        // Create wallet for the reseller
        Wallet::create(['user_id' => $user->id]);

        // Sync to Firestore
        $this->syncUserToFirestore($user->fresh()->load('wallet'));

        // Send OTP
        $otp = $user->generateOtp();
        $this->sendOtp($user->phone, $otp);

        $response = [
            'message' => 'تم إنشاء الحساب. يرجى إدخال رمز التحقق المرسل إلى هاتفك.',
            'phone'   => $user->phone,
        ];

        // TODO: remove OTP from response before public launch
        $response['otp'] = $otp;

        return response()->json($response, 201);
    }

    /**
     * Verify OTP and activate account
     */
    public function verifyOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
            'otp'   => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'رقم الهاتف غير موجود'], 404);
        }

        if ($user->otp_code !== $request->otp) {
            return response()->json(['message' => 'رمز التحقق غير صحيح'], 400);
        }

        if (now()->isAfter($user->otp_expires_at)) {
            return response()->json(['message' => 'انتهت صلاحية رمز التحقق'], 400);
        }

        $user->update([
            'phone_verified' => true,
            'status'         => 'active',
            'otp_code'       => null,
            'otp_expires_at' => null,
        ]);

        // Sync to Firestore (account now active)
        $this->syncUserToFirestore($user->fresh()->load('wallet'));

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'تم تفعيل الحساب بنجاح',
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    /**
     * Login with phone + password
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone'    => 'required|string',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'رقم الهاتف أو كلمة المرور غير صحيحة'], 401);
        }

        if (!$user->phone_verified) {
            return response()->json(['message' => 'يرجى تفعيل حسابك أولاً'], 403);
        }

        if ($user->status === 'banned') {
            return response()->json(['message' => 'حسابك محظور. تواصل مع الإدارة'], 403);
        }

        // Update FCM token if provided
        if ($request->filled('fcm_token')) {
            $user->update(['fcm_token' => $request->fcm_token]);
        }

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'message' => 'مرحباً ' . $user->name,
            'token'   => $token,
            'user'    => $this->formatUser($user),
        ]);
    }

    /**
     * Resend OTP
     */
    public function resendOtp(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::where('phone', $request->phone)->first();

        if (!$user) {
            return response()->json(['message' => 'رقم الهاتف غير موجود'], 404);
        }

        // Rate limit: don't resend if OTP is younger than 60 seconds
        if ($user->otp_expires_at && now()->diffInSeconds($user->otp_expires_at) > 540) {
            return response()->json(['message' => 'انتظر دقيقة قبل إعادة الإرسال'], 429);
        }

        $otp = $user->generateOtp();
        $this->sendOtp($user->phone, $otp);

        $response = ['message' => 'تم إرسال رمز التحقق'];

        // TODO: remove OTP from response before public launch
        $response['otp'] = $otp;

        return response()->json($response);
    }

    /**
     * Get current authenticated user profile
     */
    public function profile(Request $request): JsonResponse
    {
        $user = $request->user()->load('wallet');
        $w = $user->wallet;

        return response()->json([
            'user'   => $this->formatUser($user),
            'wallet' => [
                'balance'         => $w ? $w->balance : 0,
                'total_earned'    => $w ? $w->total_earned : 0,
                'total_withdrawn' => $w ? $w->total_withdrawn : 0,
            ],
        ]);
    }

    /**
     * Update profile
     */
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $request->user();

        $validator = Validator::make($request->all(), [
            'name' => 'sometimes|string|max:100',
            'city' => 'sometimes|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user->update($request->only(['name', 'city']));

        // Sync to Firestore
        $this->syncUserToFirestore($user->fresh()->load('wallet'));

        return response()->json([
            'message' => 'تم تحديث البيانات بنجاح',
            'user'    => $this->formatUser($user->fresh()),
        ]);
    }

    /**
     * Change password
     */
    public function changePassword(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json(['message' => 'كلمة المرور الحالية غير صحيحة'], 400);
        }

        $user->update(['password' => Hash::make($request->new_password)]);

        return response()->json(['message' => 'تم تغيير كلمة المرور بنجاح']);
    }

    /**
     * Logout
     */
    public function logout(Request $request): JsonResponse
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        return response()->json(['message' => 'تم تسجيل الخروج بنجاح']);
    }

    /**
     * Update Firebase FCM token
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'present|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();
        $user->update(['fcm_token' => $request->fcm_token ?: null]);

        // Sync FCM token to Firestore
        $this->fs->update('users', (string) $user->id, ['fcm_token' => $request->fcm_token ?: null]);

        return response()->json(['message' => 'تم تحديث رمز الإشعارات']);
    }

    /**
     * Upload profile avatar
     */
    public function uploadAvatar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'avatar' => 'required|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = $request->user();

        // Delete old avatar if exists
        if ($user->avatar && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->avatar)) {
            \Illuminate\Support\Facades\Storage::disk('public')->delete($user->avatar);
        }

        $path = $request->file('avatar')->store('avatars', 'public');
        $user->update(['avatar' => $path]);

        $avatarUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($path);

        // Sync avatar to Firestore
        $this->fs->update('users', (string) $user->id, ['avatar' => $avatarUrl]);

        return response()->json([
            'message' => 'تم تحديث الصورة الشخصية بنجاح',
            'avatar_url' => $avatarUrl,
        ]);
    }

    // --- Private helpers ---
    private function formatUser(User $user): array
    {
        return [
            'id'    => $user->id,
            'name'  => $user->name,
            'phone' => $user->phone,
            'city'  => $user->city,
            'role'  => $user->role,
            'status' => $user->status,
            'phone_verified' => $user->phone_verified,
        ];
    }

    private function sendOtp(string $phone, string $otp): void
    {
        // In production: integrate with Twilio, InfoBip, or local Iraqi SMS gateway
        // For now: log the OTP (remove in production)
        \Illuminate\Support\Facades\Log::info("OTP for {$phone}: {$otp}");
    }
}
