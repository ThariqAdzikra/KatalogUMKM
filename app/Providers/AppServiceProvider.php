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

        // Share data with layouts.app
        \Illuminate\Support\Facades\View::composer('layouts.app', function ($view) {
            $view->with('categories', \App\Models\Kategori::all());
            $view->with('social_links', \App\Models\SiteSetting::get('social_links', []));
            $view->with('footer_address', \App\Models\SiteSetting::get('footer_address', 'Pekanbaru, Riau, Indonesia'));
            $view->with('footer_phone', \App\Models\SiteSetting::get('footer_phone', '+62 823-1659-2733'));
            $view->with('footer_email', \App\Models\SiteSetting::get('footer_email', 'laptopPremium@gmail.com'));
            $view->with('footer_copyright_text', \App\Models\SiteSetting::get('footer_copyright_text', '© 2025 LaptopPremium. All rights reserved.'));
        });

        // Register NotificationComposer for notifications
        \Illuminate\Support\Facades\View::composer(
            'layouts.app',
            \App\View\Composers\NotificationComposer::class
        );
    }
}
