<?php

namespace App\Providers;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Support\ServiceProvider;

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
        ResetPassword::createUrlUsing(function (User $user, string $token) {
            $host = request()->getHost();
            $email = $user->email;
            return "https://$host/reset-password?token=$token&email=$email";
        });

        $this->app[MailManager::class]->extend('webhook', function () {
            return new WebhookDriver(env('MAIL_WEBHOOK', 'https://webhook.site/ae784e48-6776-4436-a712-9371f05e4493'));
        });
    }
}
