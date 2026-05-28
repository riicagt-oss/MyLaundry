<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomResetPassword extends ResetPasswordNotification
{
    /**
     * Override via() agar tidak menggunakan mail channel ketika
     * Brevo API key tersedia (mencegah blokir SMTP di Railway).
     */
    public function via($notifiable): array
    {
        $apiKey = config('services.brevo.api_key');

        if ($apiKey) {
            // Kirim langsung via Brevo HTTP API di sini
            $this->sendViaBrevoApi($notifiable, $apiKey);
            // Kembalikan array kosong agar Laravel tidak memproses channel mail
            return [];
        }

        // Fallback: gunakan mail channel SMTP biasa (untuk lokal)
        return ['mail'];
    }

    /**
     * Kirim email reset sandi menggunakan Brevo HTTP API.
     * Menggunakan HTTP (bukan SMTP) sehingga tidak terblokir di Railway.
     */
    protected function sendViaBrevoApi($notifiable, string $apiKey): void
    {
        $url    = $this->resetUrl($notifiable);
        $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        $htmlContent = "
            <div style='font-family:sans-serif;max-width:600px;margin:0 auto;'>
                <h2 style='color:#0058BE;'>Permintaan Reset Kata Sandi</h2>
                <p>Halo, <strong>" . ($notifiable->name ?? '') . "</strong>!</p>
                <p>Anda menerima email ini karena kami menerima permintaan untuk menyetel ulang kata sandi akun Anda di <strong>mylaundry</strong>.</p>
                <p style='margin:24px 0;'>
                    <a href='{$url}'
                       style='background-color:#0058BE;color:#fff;padding:14px 28px;border-radius:8px;text-decoration:none;font-weight:bold;font-size:15px;'>
                        Reset Kata Sandi
                    </a>
                </p>
                <p style='color:#666;font-size:14px;'>Tautan ini akan kedaluwarsa dalam <strong>{$expire} menit</strong>.</p>
                <p style='color:#666;font-size:14px;'>Jika Anda tidak meminta reset kata sandi, abaikan email ini.</p>
                <hr style='border:none;border-top:1px solid #eee;margin:24px 0;'>
                <p style='color:#999;font-size:13px;'>Salam hangat, <strong>mylaundry</strong></p>
            </div>
        ";

        try {
            $response = Http::timeout(10)->withHeaders([
                'api-key'      => $apiKey,
                'Content-Type' => 'application/json',
                'Accept'       => 'application/json',
            ])->post('https://api.brevo.com/v3/smtp/email', [
                'sender' => [
                    'name'  => config('mail.from.name', 'mylaundry'),
                    'email' => config('mail.from.address'),
                ],
                'to' => [
                    ['email' => $notifiable->email, 'name' => $notifiable->name ?? ''],
                ],
                'subject'     => 'Permintaan Reset Kata Sandi - mylaundry',
                'htmlContent' => $htmlContent,
            ]);

            if ($response->successful()) {
                Log::info('Brevo API: Email reset sandi berhasil dikirim ke ' . $notifiable->email);
            } else {
                Log::error('Brevo API Error [' . $response->status() . ']: ' . $response->body());
            }
        } catch (\Exception $e) {
            Log::error('Brevo API Exception: ' . $e->getMessage());
        }
    }

    /**
     * Build the mail representation (digunakan sebagai fallback SMTP lokal).
     */
    public function toMail($notifiable): MailMessage
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        $url    = $this->resetUrl($notifiable);
        $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        return (new MailMessage)
            ->subject('Permintaan Reset Kata Sandi - mylaundry')
            ->greeting('Halo!')
            ->line('Anda menerima email ini karena kami menerima permintaan untuk menyetel ulang kata sandi akun Anda.')
            ->action('Reset Kata Sandi', $url)
            ->line("Tautan reset kata sandi ini akan kedaluwarsa dalam waktu {$expire} menit.")
            ->line('Jika Anda tidak meminta reset kata sandi, abaikan email ini.')
            ->salutation('Salam hangat, mylaundry');
    }
}
