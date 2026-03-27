<?php

namespace App\Services;

use App\Models\SmsLog;
use App\Models\SmsTemplate;
use App\Models\User;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class SmsService
{
    public static function sendCertificatePickupNotice(User $user, string $certificateType, int $referenceId): void
    {
        $default = 'Magandang araw {name}! Ang iyong {request_type} request (#{reference_id}) ay RELEASED at ready for pickup sa {pickup_location}. Dalhin ang valid ID at claim stub. Salamat.';
        self::sendTemplateToUser(
            $user,
            'certificate_released_pickup',
            [
                'name' => $user->full_name ?: trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'request_type' => $certificateType,
                'reference_id' => (string) $referenceId,
                'pickup_location' => 'Barangay Hall',
            ],
            $default,
            [
                'module' => 'certificate',
                'reference_id' => $referenceId,
            ]
        );
    }

    public static function sendPermitPickupNotice(User $user, string $permitType, int $referenceId): void
    {
        $default = 'Magandang araw {name}! Ang iyong {request_type} request (#{reference_id}) ay RELEASED at ready for pickup sa {pickup_location}. Dalhin ang valid ID at claim stub. Salamat.';
        self::sendTemplateToUser(
            $user,
            'permit_released_pickup',
            [
                'name' => $user->full_name ?: trim(($user->first_name ?? '') . ' ' . ($user->last_name ?? '')),
                'request_type' => $permitType,
                'reference_id' => (string) $referenceId,
                'pickup_location' => 'Barangay Hall',
            ],
            $default,
            [
                'module' => 'permit',
                'reference_id' => $referenceId,
            ]
        );
    }

    public static function sendTestMessage(string $mobile, string $templateKey, array $variables, string $defaultMessage): array
    {
        $message = self::resolveTemplateMessage($templateKey, $variables, $defaultMessage);

        return self::sendToMobile($mobile, $message, null, $templateKey, [
            'module' => 'sms_test',
            'reference_id' => null,
        ]);
    }

    public static function sendTemplateToUser(
        User $user,
        string $templateKey,
        array $variables,
        string $defaultMessage,
        array $context = []
    ): array {
        $message = self::resolveTemplateMessage($templateKey, $variables, $defaultMessage);

        return self::sendToMobile((string) $user->contact_number, $message, $user, $templateKey, $context);
    }

    public static function sendToUser(User $user, string $message, array $context = []): void
    {
        self::sendToMobile((string) $user->contact_number, $message, $user, null, $context);
    }

    private static function sendToMobile(
        string $rawMobile,
        string $message,
        ?User $user = null,
        ?string $templateKey = null,
        array $context = []
    ): array {
        $provider = 'easysendsms';

        if (! config('services.sms.enabled')) {
            self::logSms($user?->id, $rawMobile, $templateKey, $message, 'skipped', $provider, 'SMS disabled in configuration.', $context);

            return ['status' => 'skipped'];
        }

        $mobile = self::normalizePhMobile($rawMobile, true);
        if (! $mobile) {
            Log::warning('SMS skipped: invalid or missing mobile number.', array_merge($context, [
                'user_id' => $user?->id,
                'raw_contact_number' => $rawMobile,
                'provider' => $provider,
            ]));
            self::logSms($user?->id, $rawMobile, $templateKey, $message, 'skipped', $provider, 'Invalid or missing mobile number.', $context);

            return ['status' => 'skipped'];
        }

        $apiKey = (string) config('services.sms.api_key');
        $baseUrl = rtrim((string) config('services.sms.base_url', 'https://restapi.easysendsms.app'), '/');
        $sender = config('services.sms.sender_name');

        if ($apiKey === '') {
            Log::warning('SMS skipped: missing API key.', array_merge($context, ['user_id' => $user?->id]));
            self::logSms($user?->id, $mobile, $templateKey, $message, 'skipped', $provider, 'Missing API key.', $context);

            return ['status' => 'skipped'];
        }

        try {
            $client = Http::timeout(15);
            $payload = [
                'to' => $mobile,
                'text' => $message,
                'type' => '0',
            ];
            if (is_string($sender) && trim($sender) !== '') {
                $payload['from'] = trim($sender);
            }

            $endpoint = str_ends_with($baseUrl, '/sms/send')
                ? $baseUrl
                : $baseUrl . '/v1/rest/sms/send';

            $response = $client
                ->acceptJson()
                ->withHeaders(['apikey' => $apiKey])
                ->asJson()
                ->post($endpoint, $payload);

            if (! $response->successful()) {
                Log::warning('SMS send failed.', array_merge($context, [
                    'user_id' => $user?->id,
                    'mobile' => $mobile,
                    'provider' => $provider,
                    'http_status' => $response->status(),
                    'response' => $response->body(),
                ]));
                self::logSms($user?->id, $mobile, $templateKey, $message, 'failed', $provider, "HTTP {$response->status()}: {$response->body()}", $context);

                return ['status' => 'failed'];
            }

            // Some providers may return HTTP 200 with validation errors in JSON body.
            $responseJson = $response->json();
            if (is_array($responseJson) && self::hasProviderError($responseJson)) {
                $errorMessage = self::extractProviderErrorMessage($responseJson) ?: $response->body();

                Log::warning('SMS provider returned error payload.', array_merge($context, [
                    'user_id' => $user?->id,
                    'mobile' => $mobile,
                    'provider' => $provider,
                    'response' => $responseJson,
                ]));

                self::logSms($user?->id, $mobile, $templateKey, $message, 'failed', $provider, $errorMessage, $context);

                return ['status' => 'failed'];
            }

            self::logSms($user?->id, $mobile, $templateKey, $message, 'sent', $provider, $response->body(), $context);

            return ['status' => 'sent'];
        } catch (\Throwable $e) {
            Log::error('SMS send exception.', array_merge($context, [
                'user_id' => $user?->id,
                'mobile' => $mobile,
                'provider' => $provider,
                'error' => $e->getMessage(),
            ]));
            self::logSms($user?->id, $mobile, $templateKey, $message, 'failed', $provider, $e->getMessage(), $context);

            return ['status' => 'failed'];
        }
    }

    private static function hasProviderError(array $payload): bool
    {
        // EasySendSMS commonly returns:
        // { "error": 4012, "description": "..."} or {"status":"error","message":"..."}.
        if (array_key_exists('error', $payload)) {
            return true;
        }

        if (
            isset($payload['status'])
            && is_string($payload['status'])
            && strtolower($payload['status']) === 'error'
        ) {
            return true;
        }

        return array_key_exists('description', $payload);
    }

    private static function extractProviderErrorMessage(array $payload): ?string
    {
        if (isset($payload['error']) && is_string($payload['error']) && trim($payload['error']) !== '') {
            return $payload['error'];
        }

        if (isset($payload['description']) && is_string($payload['description']) && trim($payload['description']) !== '') {
            return $payload['description'];
        }

        $parts = [];
        foreach (['description', 'message'] as $field) {
            if (! array_key_exists($field, $payload)) {
                continue;
            }

            $value = $payload[$field];
            if (is_array($value)) {
                $parts[] = $field . ': ' . implode(' | ', array_map('strval', $value));
            } else {
                $parts[] = $field . ': ' . (string) $value;
            }
        }

        return $parts ? implode('; ', $parts) : null;
    }

    private static function resolveTemplateMessage(string $templateKey, array $variables, string $defaultMessage): string
    {
        $templateMessage = $defaultMessage;

        if (Schema::hasTable('sms_templates')) {
            $template = SmsTemplate::query()
                ->where('key', $templateKey)
                ->where('is_active', true)
                ->first();

            if ($template && trim((string) $template->message) !== '') {
                $templateMessage = (string) $template->message;
            }
        }

        $replace = [];
        foreach ($variables as $key => $value) {
            $replace['{' . $key . '}'] = (string) $value;
        }

        return strtr($templateMessage, $replace);
    }

    private static function logSms(
        ?int $userId,
        string $mobile,
        ?string $templateKey,
        string $message,
        string $status,
        string $provider,
        ?string $providerResponse,
        array $context = []
    ): void {
        if (! Schema::hasTable('sms_logs')) {
            return;
        }

        SmsLog::query()->create([
            'user_id' => $userId,
            'mobile' => $mobile,
            'template_key' => $templateKey,
            'message' => $message,
            'status' => $status,
            'provider' => $provider,
            'provider_response' => $providerResponse,
            'context_type' => $context['module'] ?? null,
            'context_id' => $context['reference_id'] ?? null,
            'sent_at' => $status === 'sent' ? now() : null,
        ]);
    }

    public static function normalizeForDisplay(string $value): ?string
    {
        return self::normalizePhMobile($value);
    }

    private static function normalizePhMobile(string $value, bool $international = false): ?string
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', $trimmed) ?? '';

        if (str_starts_with($digits, '63') && strlen($digits) === 12) {
            $digits = '0' . substr($digits, 2);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '9')) {
            $digits = '0' . $digits;
        }

        if (preg_match('/^09\d{9}$/', $digits) !== 1) {
            return null;
        }

        if ($international) {
            return '63' . substr($digits, 1);
        }

        return $digits;
    }
}
