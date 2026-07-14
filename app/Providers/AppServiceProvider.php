<?php

namespace App\Providers;

use App\Modules\Notifications\Application\Contracts\NotificationChannel;
use App\Modules\Notifications\Application\NotificationDispatcher;
use App\Modules\Notifications\Infrastructure\Channels\FcmPushNotificationChannel;
use App\Modules\Notifications\Infrastructure\Channels\InAppNotificationChannel;
use App\Modules\Notifications\Infrastructure\Channels\MailNotificationChannel;
use App\Modules\Notifications\Infrastructure\Channels\SmsNotificationChannel;
use App\Modules\Wallet\Application\Contracts\PaymentGateway;
use App\Modules\Wallet\Infrastructure\Payments\SandboxPaymentGateway;
use App\Modules\Wallet\Infrastructure\Payments\StripePaymentGateway;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerPaymentGateway();
        $this->registerNotificationChannels();
    }

    public function boot(): void
    {
        RateLimiter::for('api', fn (Request $request) => Limit::perMinute(120)->by($request->user()?->id ?: $request->ip()));
    }

    private function registerPaymentGateway(): void
    {
        $this->app->bind(PaymentGateway::class, function ($app): PaymentGateway {
            if (config('services.payments.driver') === 'stripe') {
                return new StripePaymentGateway($app->make(HttpFactory::class), [
                    'secret' => (string) config('services.stripe.secret'),
                    'base_url' => (string) config('services.stripe.base_url'),
                    'success_url' => (string) config('services.stripe.success_url'),
                    'cancel_url' => (string) config('services.stripe.cancel_url'),
                ]);
            }

            return new SandboxPaymentGateway;
        });
    }

    private function registerNotificationChannels(): void
    {
        $this->app->tag([
            InAppNotificationChannel::class,
            MailNotificationChannel::class,
            FcmPushNotificationChannel::class,
            SmsNotificationChannel::class,
        ], NotificationChannel::class);

        $this->app->singleton(NotificationDispatcher::class, fn ($app) => new NotificationDispatcher(
            $app->tagged(NotificationChannel::class),
        ));
    }
}
