<?php

namespace App\Providers;

use App\Mail\Driver\WebhookDriver;
use App\Models\User;
use App\Services\WhatsApiService;
use GuzzleHttp\Client;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Mail\MailManager;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind('whatsapi', function () {
            return new WhatsApiService(
                client: new Client(),
                endpoint: config('services.api_gate.whatsapi_data_url'),
                token: config('services.whatsapi.token')
            );
        });
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
            return new WebhookDriver(config('mail.mailers.webhook_reg.url'));
        });
    }
}
