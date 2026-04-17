<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PushNotification;
use App\Models\User;
use App\Services\FcmService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class NotificationController extends Controller
{
    public function __construct(private FcmService $fcm) {}

    public function index(): View
    {
        $notifications = PushNotification::with('creator')
            ->latest()
            ->paginate(20);

        $resellersCount = User::where('role', 'reseller')
            ->where('status', 'active')
            ->whereNotNull('fcm_token')
            ->count();

        $deliveryCount = User::where('role', 'delivery')
            ->whereNotNull('fcm_token')
            ->count();

        $allUsers = User::whereNotNull('fcm_token')
            ->select('id', 'name', 'phone', 'role')
            ->orderBy('name')
            ->get();

        return view('admin.notifications.index', compact(
            'notifications', 'resellersCount', 'deliveryCount', 'allUsers'
        ));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'title'          => 'required|string|max:100',
            'body'           => 'required|string|max:500',
            'target_type'    => 'required|in:all,role,user',
            'target_role'    => 'required_if:target_type,role|nullable|in:reseller,delivery',
            'target_user_id' => 'required_if:target_type,user|nullable|exists:users,id',
            'click_action'   => 'nullable|string|max:100',
            'image'          => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
        ]);

        // Upload image if provided
        $imageUrl = null;
        if ($request->hasFile('image')) {
            $path     = $request->file('image')->store('notifications', 'public');
            $imageUrl = url(Storage::url($path));
        }

        // Resolve target tokens
        $query = User::whereNotNull('fcm_token')->where('status', 'active');

        if ($request->target_type === 'role') {
            $query->where('role', $request->target_role);
        } elseif ($request->target_type === 'user') {
            $query->where('id', $request->target_user_id);
        }

        $tokens = $query->pluck('fcm_token')->filter()->values()->toArray();

        if (empty($tokens)) {
            return back()->with('error', 'لا يوجد مستخدمون لديهم إشعارات مفعّلة.');
        }

        $data = ['type' => 'admin_notification'];
        if ($request->click_action) {
            $data['route'] = $request->click_action;
        }

        // Send via FCM
        $result = $this->fcm->sendToTokens(
            tokens:   $tokens,
            title:    $request->title,
            body:     $request->body,
            imageUrl: $imageUrl,
            data:     $data,
        );

        // Store record
        PushNotification::create([
            'title'          => $request->title,
            'body'           => $request->body,
            'image_url'      => $imageUrl,
            'target_type'    => $request->target_type,
            'target_role'    => $request->target_role,
            'target_user_id' => $request->target_user_id,
            'click_action'   => $request->click_action,
            'data'           => $data,
            'sent_count'     => $result['sent'],
            'failed_count'   => $result['failed'],
            'sent_at'        => now(),
            'created_by'     => auth()->id(),
        ]);

        $total = count($tokens);

        return back()->with(
            'success',
            "✅ تم الإرسال إلى {$result['sent']} من {$total} مستخدم." .
            ($result['failed'] > 0 ? " ⚠️ فشل {$result['failed']}." : '')
        );
    }
}


class NotificationController extends Controller
{
    public function index(): View
    {
        $resellers = User::where('role', 'reseller')
            ->where('status', 'active')
            ->whereNotNull('fcm_token')
            ->select('id', 'name', 'phone', 'city')
            ->get();

        $totalActive = User::where('role', 'reseller')->where('status', 'active')->count();

        return view('admin.notifications.index', compact('resellers', 'totalActive'));
    }

    public function send(Request $request): RedirectResponse
    {
        $request->validate([
            'title'   => 'required|string|max:100',
            'body'    => 'required|string|max:500',
            'target'  => 'required|in:all,selected',
            'user_ids' => 'required_if:target,selected|array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $query = User::where('role', 'reseller')
            ->where('status', 'active')
            ->whereNotNull('fcm_token');

        if ($request->target === 'selected') {
            $query->whereIn('id', $request->user_ids);
        }

        $tokens = $query->pluck('fcm_token')->filter()->values()->toArray();

        if (empty($tokens)) {
            return redirect()->route('admin.notifications.index')
                ->with('error', 'لا يوجد بائعون لديهم إشعارات مفعّلة');
        }

        $sent = $this->sendFcmNotification(
            $tokens,
            $request->title,
            $request->body
        );

        if ($sent) {
            return redirect()->route('admin.notifications.index')
                ->with('success', "تم إرسال الإشعار إلى {$sent} بائع");
        }

        return redirect()->route('admin.notifications.index')
            ->with('error', 'فشل إرسال الإشعار. تحقق من إعدادات Firebase.');
    }

    /**
     * Send FCM notification via Firebase HTTP v1 API.
     * Uses deprecated legacy API for simplicity — upgrade to v1 if needed.
     */
    private function sendFcmNotification(array $tokens, string $title, string $body): int|false
    {
        $serverKey = config('services.fcm.server_key');

        if (!$serverKey) {
            Log::warning('[FCM] FCM_SERVER_KEY not configured');
            return false;
        }

        $chunks = array_chunk($tokens, 500); // FCM max 500 tokens per request
        $totalSent = 0;

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $serverKey,
                    'Content-Type'  => 'application/json',
                ])->post('https://fcm.googleapis.com/fcm/send', [
                    'registration_ids' => $chunk,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                        'sound' => 'default',
                    ],
                    'data' => [
                        'title' => $title,
                        'body'  => $body,
                        'type'  => 'admin_notification',
                    ],
                    'priority' => 'high',
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $totalSent += ($result['success'] ?? 0);
                } else {
                    Log::error('[FCM] Send failed', ['status' => $response->status(), 'body' => $response->body()]);
                }
            } catch (\Throwable $e) {
                Log::error('[FCM] Exception: ' . $e->getMessage());
            }
        }

        return $totalSent > 0 ? $totalSent : false;
    }
}
