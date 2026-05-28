<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class CustomResetPassword extends ResetPasswordNotification
{
    /**
     * Send the notification. Override agar menggunakan Brevo HTTP API
     * (menghindari blokir port SMTP di Railway/hosting).
     *
     * @param  mixed  $notifiable
     * @return void
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        $url = $this->resetUrl($notifiable);
        $expire = config('auth.passwords.' . config('auth.defaults.passwords') . '.expire', 60);

        $apiKey = config('services.brevo.api_key');

        // Jika API key Brevo tersedia, gunakan HTTP API (tidak terblokir di hosting)
        if ($apiKey) {
            $htmlContent = "
                <h2>Permintaan Reset Kata Sandi - mylaundry</h2>
                <p>Halo!</p>
                <p>Anda menerima email ini karena kami menerima permintaan untuk menyetel ulang kata sandi akun Anda.</p>
                <p style='margin: 24px 0;'>
                    <a href='{$url}' style='background-color:#0058BE;color:#fff;padding:12px 24px;border-radius:6px;text-decoration:none;font-weight:bold;'>
                        Reset Kata Sandi
                    </a>
                </p>
                <p>Tautan reset kata sandi ini akan kedaluwarsa dalam waktu {$expire} menit.</p>
                <p>Jika Anda tidak meminta reset kata sandi, abaikan email ini.</p>
                <br>
                <p>Salam hangat, mylaundry</p>
            ";

            try {
                $response = Http::withHeaders([
                    'api-key' => $apiKey,
                    'Content-Type' => 'application/json',
                ])->post('https://api.brevo.com/v3/smtp/email', [
                    'sender' => [
                        'name' => config('mail.from.name', 'mylaundry'),
                        'email' => config('mail.from.address'),
                    ],
                    'to' => [
                        ['email' => $notifiable->email, 'name' => $notifiable->name ?? ''],
                    ],
                    'subject' => 'Permintaan Reset Kata Sandi - mylaundry',
                    'htmlContent' => $htmlContent,
                ]);

                if (!$response->successful()) {
                    Log::error('Brevo API Error: ' . $response->body());
                }

                return null; // Email sudah dikirim via API, tidak perlu MailMessage
            } catch (\Exception $e) {
                Log::error('Brevo API Exception: ' . $e->getMessage());
            }
        }

        // Fallback ke SMTP biasa jika API key tidak diset
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
