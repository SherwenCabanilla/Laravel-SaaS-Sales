<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class TransactionalEmailService
{
    /**
     * @param  array<string, mixed>  $meta
     * @return array{sent: bool, provider: string}
     */
    public function sendPlainText(string $recipient, string $subject, string $body, array $meta = []): array
    {
        $apiKey = ltrim(trim((string) config('services.brevo.api_key')), '=');
        $senderEmail = trim((string) config('mail.from.address'));
        $senderName = trim((string) config('mail.from.name'));

        if ($apiKey !== '' && $senderEmail !== '') {
            try {
                $response = Http::timeout(15)
                    ->acceptJson()
                    ->withHeaders([
                        'api-key' => $apiKey,
                    ])
                    ->post('https://api.brevo.com/v3/smtp/email', [
                        'sender' => array_filter([
                            'email' => $senderEmail,
                            'name' => $senderName !== '' ? $senderName : null,
                        ]),
                        'to' => [
                            ['email' => $recipient],
                        ],
                        'subject' => $subject,
                        'textContent' => $body,
                    ]);

                if ($response->successful()) {
                    return ['sent' => true, 'provider' => 'brevo'];
                }

                Log::warning('Brevo transactional email send failed.', [
                    'recipient' => $recipient,
                    'status' => $response->status(),
                    'body' => $response->json() ?: $response->body(),
                    'meta' => $meta,
                ]);
            } catch (\Throwable $e) {
                Log::warning('Brevo transactional email send exception.', [
                    'recipient' => $recipient,
                    'error' => $e->getMessage(),
                    'meta' => $meta,
                ]);
            }
        }

        try {
            Mail::raw($body, function ($message) use ($recipient, $subject) {
                $message->to($recipient)->subject($subject);
            });

            return ['sent' => true, 'provider' => (string) config('mail.default', 'log')];
        } catch (\Throwable $e) {
            Log::warning('Transactional email fallback mail send failed.', [
                'recipient' => $recipient,
                'error' => $e->getMessage(),
                'meta' => $meta,
            ]);
        }

        return ['sent' => false, 'provider' => (string) config('mail.default', 'log')];
    }
}
