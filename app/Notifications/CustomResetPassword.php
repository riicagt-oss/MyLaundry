<?php

namespace App\Notifications;

use Illuminate\Auth\Notifications\ResetPassword as ResetPasswordNotification;
use Illuminate\Notifications\Messages\MailMessage;

class CustomResetPassword extends ResetPasswordNotification
{
    /**
     * Build the mail representation of the notification.
     *
     * @param  mixed  $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        if (static::$toMailCallback) {
            return call_user_func(static::$toMailCallback, $notifiable, $this->token);
        }

        $url = $this->resetUrl($notifiable);
        $expire = config('auth.passwords.'.config('auth.defaults.passwords').'.expire', 60);

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
