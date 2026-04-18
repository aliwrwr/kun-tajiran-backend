<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Firestore REST API Service
 *
 * No external packages required — uses PHP built-in OpenSSL for JWT signing.
 * Wraps the Firestore REST API v1.
 *
 * Setup:
 *  1. Set FIREBASE_PROJECT_ID in .env
 *  2. Place firebase-service-account.json in storage/app/
 *
 * Firestore REST base: https://firestore.googleapis.com/v1/projects/{project}/databases/(default)/documents
 */
class FirestoreService
{
    /** @var string */
    private $projectId;

    /** @var string */
    private $baseUrl;

    /** @var array */
    private $sslOptions;

    public function __construct()
    {
        $this->projectId = config('services.firebase.project_id', '');
        $this->baseUrl   = 'https://firestore.googleapis.com/v1/projects/' . $this->projectId . '/databases/(default)/documents';
        $this->sslOptions = ['verify' => false];
    }

    // ──────────────────────────────────────────────────────
    // CRUD Operations
    // ──────────────────────────────────────────────────────

    /**
     * Get a single document.
     * Returns the document fields as a plain PHP array, or null if not found.
     */
    public function get(string $collection, string $docId): ?array
    {
        $response = Http::withOptions($this->sslOptions)->withToken($this->getAccessToken())
            ->get("{$this->baseUrl}/{$collection}/{$docId}");

        if ($response->status() === 404) return null;
        if (!$response->successful()) {
            $this->logError('get', $response);
            return null;
        }

        return $this->decodeDocument($response->json());
    }

    /**
     * List documents in a collection.
     * Returns array of ['id' => string, ...fields].
     * $filters: [['field', 'op', 'value'], ...]
     * $orderBy: ['field' => 'ASCENDING'|'DESCENDING']
     */
    public function list(
        string  $collection,
        array   $filters  = [],
        ?string $orderBy  = null,
        string  $direction = 'ASCENDING',
        int     $limit    = 100,
        ?string $pageToken = null,
    ): array {
        // Use structured query (runQuery) when filters are present
        if (!empty($filters) || $orderBy || $limit) {
            return $this->runQuery($collection, $filters, $orderBy, $direction, $limit, $pageToken);
        }

        $response = Http::withOptions($this->sslOptions)->withToken($this->getAccessToken())
            ->get("{$this->baseUrl}/{$collection}", array_filter([
                'pageSize'  => $limit,
                'pageToken' => $pageToken,
            ]));

        if (!$response->successful()) {
            $this->logError('list', $response);
            return [];
        }

        $data = $response->json();
        return array_map(
            fn($doc) => $this->decodeDocument($doc),
            $data['documents'] ?? []
        );
    }

    /**
     * Create a document with auto-generated ID.
     * Returns the created document with 'id' field.
     */
    public function create(string $collection, array $data): ?array
    {
        $token = $this->getAccessToken();
        if (empty($token)) return null;
        $response = Http::withOptions($this->sslOptions)->withToken($token)
            ->post("{$this->baseUrl}/{$collection}", [
                'fields' => $this->encodeFields($data),
            ]);

        if (!$response->successful()) {
            $this->logError('create', $response);
            return null;
        }

        return $this->decodeDocument($response->json());
    }

    /**
     * Create or overwrite a document with a specific ID.
     */
    public function set(string $collection, string $docId, array $data): bool
    {
        $token = $this->getAccessToken();
        if (empty($token)) return false;
        $response = Http::withOptions($this->sslOptions)->withToken($token)
            ->patch("{$this->baseUrl}/{$collection}/{$docId}", [
                'fields' => $this->encodeFields($data),
            ]);

        if (!$response->successful()) {
            $this->logError('set', $response);
            return false;
        }
        return true;
    }

    /**
     * Update specific fields of an existing document (partial update).
     */
    public function update(string $collection, string $docId, array $data): bool
    {
        $token = $this->getAccessToken();
        if (empty($token)) return false;
        $fieldPaths = array_keys($data);
        $query      = implode('&', array_map(
            fn($f) => 'updateMask.fieldPaths=' . urlencode($f),
            $fieldPaths
        ));

        $response = Http::withOptions($this->sslOptions)->withToken($token)
            ->patch("{$this->baseUrl}/{$collection}/{$docId}?{$query}", [
                'fields' => $this->encodeFields($data),
            ]);

        if (!$response->successful()) {
            $this->logError('update', $response);
            return false;
        }
        return true;
    }

    /**
     * Delete a document.
     */
    public function delete(string $collection, string $docId): bool
    {
        $token = $this->getAccessToken();
        if (empty($token)) return false;
        $response = Http::withOptions($this->sslOptions)->withToken($token)
            ->delete("{$this->baseUrl}/{$collection}/{$docId}");

        if (!$response->successful() && $response->status() !== 404) {
            $this->logError('delete', $response);
            return false;
        }
        return true;
    }

    /**
     * Run a structured query (supports filters, ordering, limit).
     * Returns array of decoded documents.
     */
    public function runQuery(
        string  $collection,
        array   $filters    = [],
        ?string $orderBy    = null,
        string  $direction  = 'ASCENDING',
        int     $limit      = 100,
        ?string $startAfter = null,
    ): array {
        $query = [
            'from'  => [['collectionId' => $collection]],
            'limit' => $limit,
        ];

        // WHERE filters
        if (!empty($filters)) {
            $firestoreFilters = array_map(function ($f) {
                [$field, $op, $value] = $f;
                return [
                    'fieldFilter' => [
                        'field'  => ['fieldPath' => $field],
                        'op'     => $this->mapOperator($op),
                        'value'  => $this->encodeValue($value),
                    ],
                ];
            }, $filters);

            $query['where'] = count($firestoreFilters) === 1
                ? $firestoreFilters[0]
                : ['compositeFilter' => ['op' => 'AND', 'filters' => $firestoreFilters]];
        }

        // ORDER BY
        if ($orderBy) {
            $query['orderBy'] = [[
                'field'     => ['fieldPath' => $orderBy],
                'direction' => $direction,
            ]];
        }

        $url      = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents:runQuery";
        $response = Http::withOptions($this->sslOptions)->withToken($this->getAccessToken())
            ->post($url, ['structuredQuery' => $query]);

        if (!$response->successful()) {
            $this->logError('runQuery', $response);
            return [];
        }

        $results = [];
        foreach ($response->json() as $row) {
            if (isset($row['document'])) {
                $results[] = $this->decodeDocument($row['document']);
            }
        }
        return $results;
    }

    /**
     * Get documents by an array of IDs (batch get).
     * Returns associative array ['docId' => data].
     */
    public function batchGet(string $collection, array $docIds): array
    {
        $names = array_map(
            fn($id) => "{$this->baseUrl}/{$collection}/{$id}",
            $docIds
        );

        $url      = "https://firestore.googleapis.com/v1/projects/{$this->projectId}/databases/(default)/documents:batchGet";
        $response = Http::withOptions($this->sslOptions)->withToken($this->getAccessToken())
            ->post($url, ['documents' => $names]);

        if (!$response->successful()) {
            $this->logError('batchGet', $response);
            return [];
        }

        $results = [];
        foreach ($response->json() as $row) {
            if (isset($row['found'])) {
                $doc              = $this->decodeDocument($row['found']);
                $results[$doc['id']] = $doc;
            }
        }
        return $results;
    }

    // ──────────────────────────────────────────────────────
    // Value Encoding / Decoding (Firestore ↔ PHP)
    // ──────────────────────────────────────────────────────

    /**
     * Encode a PHP array into Firestore field map.
     */
    public function encodeFields(array $data): array
    {
        $fields = [];
        foreach ($data as $key => $value) {
            $fields[$key] = $this->encodeValue($value);
        }
        return $fields;
    }

    private function encodeValue(mixed $value): array
    {
        if (is_null($value))   return ['nullValue' => null];
        if (is_bool($value))   return ['booleanValue' => $value];
        if (is_int($value))    return ['integerValue' => (string) $value];
        if (is_float($value))  return ['doubleValue' => $value];
        if (is_array($value)) {
            // If array is numeric/list → arrayValue
            if (array_is_list($value)) {
                return ['arrayValue' => ['values' => array_map([$this, 'encodeValue'], $value)]];
            }
            // Associative → mapValue
            return ['mapValue' => ['fields' => $this->encodeFields($value)]];
        }
        if ($value instanceof \DateTimeInterface) {
            return ['timestampValue' => $value->format(\DateTimeInterface::RFC3339_EXTENDED)];
        }
        return ['stringValue' => (string) $value];
    }

    /**
     * Decode a Firestore document JSON into a plain PHP array.
     * Adds 'id' field from the document name.
     */
    private function decodeDocument(array $doc): array
    {
        $result = [];

        // Extract ID from resource name: .../documents/collection/DOC_ID
        if (isset($doc['name'])) {
            $parts      = explode('/', $doc['name']);
            $result['id'] = end($parts);
        }

        foreach ($doc['fields'] ?? [] as $key => $typedValue) {
            $result[$key] = $this->decodeValue($typedValue);
        }

        // Timestamps
        if (isset($doc['createTime'])) $result['created_at'] = $doc['createTime'];
        if (isset($doc['updateTime'])) $result['updated_at'] = $doc['updateTime'];

        return $result;
    }

    private function decodeValue(array $typedValue): mixed
    {
        if (array_key_exists('nullValue', $typedValue))      return null;
        if (isset($typedValue['booleanValue']))   return $typedValue['booleanValue'];
        if (isset($typedValue['integerValue']))   return (int) $typedValue['integerValue'];
        if (isset($typedValue['doubleValue']))    return (float) $typedValue['doubleValue'];
        if (isset($typedValue['stringValue']))    return $typedValue['stringValue'];
        if (isset($typedValue['timestampValue'])) return $typedValue['timestampValue'];
        if (isset($typedValue['arrayValue'])) {
            return array_map(
                [$this, 'decodeValue'],
                $typedValue['arrayValue']['values'] ?? []
            );
        }
        if (isset($typedValue['mapValue'])) {
            $map = [];
            foreach ($typedValue['mapValue']['fields'] ?? [] as $k => $v) {
                $map[$k] = $this->decodeValue($v);
            }
            return $map;
        }
        return null;
    }

    private function mapOperator(string $op): string
    {
        return match ($op) {
            '=', '=='  => 'EQUAL',
            '!='       => 'NOT_EQUAL',
            '<'        => 'LESS_THAN',
            '<='       => 'LESS_THAN_OR_EQUAL',
            '>'        => 'GREATER_THAN',
            '>='       => 'GREATER_THAN_OR_EQUAL',
            'in'       => 'IN',
            'not-in'   => 'NOT_IN',
            'array-contains'     => 'ARRAY_CONTAINS',
            'array-contains-any' => 'ARRAY_CONTAINS_ANY',
            default    => 'EQUAL',
        };
    }

    // ──────────────────────────────────────────────────────
    // OAuth2 Access Token (shared with FcmService pattern)
    // ──────────────────────────────────────────────────────

    private function getAccessToken(): string
    {
        // Return cached token if still valid
        $cached = Cache::get('firestore_access_token');
        if (!empty($cached)) {
            return $cached;
        }

        $keyFile = storage_path('app/firebase-service-account.json');

        if (!file_exists($keyFile)) {
            Log::warning('FirestoreService: firebase-service-account.json not found — Firestore sync disabled.');
            return '';
        }

        $sa  = json_decode(file_get_contents($keyFile), true);
        $now = time();

        $header  = $this->base64url(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = $this->base64url(json_encode([
            'iss'   => $sa['client_email'],
            'scope' => 'https://www.googleapis.com/auth/cloud-platform',
            'aud'   => 'https://oauth2.googleapis.com/token',
            'iat'   => $now,
            'exp'   => $now + 3600,
        ]));

        $signingInput = "$header.$payload";
        $privateKey   = openssl_pkey_get_private($sa['private_key']);

        if (!$privateKey) {
            Log::error('FirestoreService: Invalid private key in service account JSON.');
            return '';
        }

        openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $jwt = "$signingInput." . $this->base64url($signature);

        $response = Http::withOptions($this->sslOptions)->asForm()->post('https://oauth2.googleapis.com/token', [
            'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
            'assertion'  => $jwt,
        ]);

        if (!$response->successful()) {
            Log::error('FirestoreService: token exchange failed: ' . $response->body());
            return '';
        }

        $token = $response->json('access_token');
        // Only cache if we got a real token
        if (!empty($token)) {
            Cache::put('firestore_access_token', $token, 55 * 60);
        }
        return $token;
    }

    private function base64url(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private function logError(string $op, $response): void
    {
        Log::error("Firestore [{$op}] failed", [
            'status' => $response->status(),
            'body'   => $response->body(),
        ]);
    }
}
