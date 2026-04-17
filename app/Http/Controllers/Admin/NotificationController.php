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
    /** @var FcmService */
    private $fcm;

    public function __construct(FcmService $fcm)
    {
        $this->fcm = $fcm;
    }

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
            $tokens,
            $request->title,
            $request->body,
            $imageUrl,
            $data
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
