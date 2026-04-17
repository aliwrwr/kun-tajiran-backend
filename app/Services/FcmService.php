<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firebase Cloud Messaging v1 HTTP API Service
 *
 * No external packages required — uses PHP built-in OpenSSL for JWT signing.
 *
 * Setup:
 *  1. Add FIREBASE_PROJECT_ID=your-project-id to .env
 *  2. Download service account JSON from Firebase Console → Project Settings → Service Accounts
 *  3. Save it as storage/app/firebase-service-account.json
 */
class FcmService
{
    /** @var string */
    private $projectId;

    /** @var string */
    private $fcmUrl;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id', '');
        $this->fcmUrl    = 'https://fcm.googleapis.com/v1/projects/' . $this->projectId . '/messages:send';
    }

    // ──────────────────────────────────────────────────────
    // Public API
    // ──────────────────────────────────────────────────────

    /** Send to a single FCM token */
    public function sendToToken(
        string  $token,
        string  $title,
        string  $body,
        ?string $imageUrl = null,
        array   $data     = [],
    ): bool {
        $msg          = $this->buildMessage($title, $body, $imageUrl, $data);
        $msg['token'] = $token;
        return $this->dispatch($msg);
    }

    /** Send to multiple tokens — returns ['sent' => int, 'failed' => int] */
    public function sendToTokens(
        array   $tokens,
        string  $title,
        string  $body,
        ?string $imageUrl = null,
        array   $data     = [],
    ): array {
        $sent   = 0;
        $failed = 0;

        foreach ($tokens as $token) {
            $msg          = $this->buildMessage($title, $body, $imageUrl, $data);
            $msg['token'] = $token;
            $this->dispatch($msg) ? $sent++ : $failed++;
        }

        return ['sent' => $sent, 'failed' => $failed];
    }

    /** Send to a Firebase topic */
    public function sendToTopic(
        string  $topic,
        string  $title,
        string  $body,
        ?string $imageUrl = null,
        array   $data     = [],
    ): bool {
        $msg          = $this->buildMessage($title, $body, $imageUrl, $data);
        $msg['topic'] = $topic;
        return $this->dispatch($msg);
    }

    // ──────────────────────────────────────────────────────
    // Private helpers
    // ──────────────────────────────────────────────────────

    private function buildMessage(string $title, string $body, ?string $imageUrl, array $data): array
    {
        $notification = ['title' => $title, 'body' => $body];
        if ($imageUrl) {
            $notification['image'] = $imageUrl;
        }

        return [
            'notification' => $notification,
            'android'      => [
                'priority'     => 'high',
                'notification' => array_filter([
                    'sound'        => 'default',
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                    'image'        => $imageUrl,
                ]),
            ],
            'apns' => [
                'payload'     => ['aps' => ['sound' => 'default', 'mutable-content' => 1]],
                'fcm_options' => $imageUrl ? ['image' => $imageUrl] : [],
            ],
            'data' => array_map('strval', $data),
        ];
    }

    private function dispatch(array $message): bool
    {
        if (empty($this->projectId)) {
            Log::warning('FCM: FIREBASE_PROJECT_ID not set in .env');
            return false;
        }

        try {
            $token    = $this->getAccessToken();
            $response = Http::withOptions($this->sslOptions)->withToken($token)
                ->post($this->fcmUrl, ['message' => $message]);

            if ($response->successful()) {
                return true;
            }

            Log::error('FCM dispatch failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);
            return false;
        } catch (\Throwable $e) {
            Log::error('FCM exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate OAuth2 access token from service account JSON.
     * Uses PHP built-in OpenSSL — no google/auth package needed.
     * Token is cached for 55 minutes (expires in 60).
     */
    private function getAccessToken(): string
    {
        return Cache::remember('fcm_access_token', 55 * 60, function () {
            $keyFile = storage_path('app/firebase-service-account.json');

            if (!file_exists($keyFile)) {
                throw new \RuntimeException(
                    'Firebase service account not found at: storage/app/firebase-service-account.json'
                );
            }

            $sa  = json_decode(file_get_contents($keyFile), true);
            $now = time();

            // Build JWT header + payload
            $header  = $this->base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = $this->base64url(json_encode([
                'iss'   => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => 'https://oauth2.googleapis.com/token',
                'iat'   => $now,
                'exp'   => $now + 3600,
            ]));

            $signingInput = "$header.$payload";

            // Sign with private key using RS256
            $privateKey = openssl_pkey_get_private($sa['private_key']);
            if (!$privateKey) {
                throw new \RuntimeException('Invalid private key in service account JSON.');
            }

            openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $jwt = "$signingInput." . $this->base64url($signature);

            // Exchange JWT for access token
            $response = Http::withOptions($this->sslOptions)->asForm()->post('https://oauth2.googleapis.com/token', [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (!$response->successful()) {
                throw new \RuntimeException('FCM token exchange failed: ' . $response->body());
            }

            return $response->json('access_token');
        });
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
