<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ResetPassword::toMailUsing(function ($notifiable, $token) {
        $url = url(route('password.reset', [
            'token' => $token,
            'email' => $notifiable->getEmailForPasswordReset(),
        ], false));

        return (new MailMessage)
            ->subject('Setel Password Akun Pegawai')
            ->greeting('Halo ' . $notifiable->name . ',')
            ->line('Akun Anda telah dibuat. Silakan setel password pertama Anda:')
            ->action('Setel Password', $url)
            ->line('Abaikan email ini jika Anda tidak meminta perubahan.');
    });
        Paginator::useBootstrapFive();
    }
}
