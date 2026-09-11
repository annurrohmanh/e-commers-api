<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;
use App\Scramble\BaseApiControllerParameterExtractor;
use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\OpenApi;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use App\Services\PasswordResetService;
use Illuminate\Contracts\Redis\Factory as RedisFactory;
use App\Models\User;
use App\Models\Catalogue;
use App\Models\Category;
use App\Policies\UserPolicy;
use App\Policies\CataloguePolicy;
use App\Policies\CategoryPolicy;
use Illuminate\Support\Facades\Gate;
use App\Services\Payment\Contracts\PaymentGatewayContract;
use App\Services\Payment\Gateways\MockPaymentGateway;
use App\Services\Shipping\Contracts\ShippingGatewayContract;
use App\Services\Shipping\Drivers\MockShippingDriver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PasswordResetService::class, function ($app) {
            return new PasswordResetService(
                $app->make(RedisFactory::class)
            );
        });

        $this->app->bind(PaymentGatewayContract::class, function () {
            return match (config('payment.driver', 'mock')) {
                // 'xendit'   => new XenditPaymentGateway(...),
                // 'midtrans' => new MidtransPaymentGateway(...),
                default    => new MockPaymentGateway(),
            };
        });

        $this->app->bind(ShippingGatewayContract::class, MockShippingDriver::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::extendOpenApi(function (OpenApi $openApi) {
            $openApi->secure(
                SecurityScheme::http('bearer')
            );
        });

        // Tambahkan custom validation untuk mendeteksi spasi
        Validator::extend('without_spaces', function ($attribute, $value, $parameters, $validator) {
            return !preg_match('/\s/', $value);
        });

        // Custom pesan error jika gagal tanpa spasi
        Validator::replacer('without_spaces', function ($message, $attribute, $rule, $parameters) {
            return str_replace(':attribute', $attribute, 'Column: Can not contain withspace.');
        });

        Gate::policy(Catalogue::class, CataloguePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(User::class, UserPolicy::class);

        Gate::before(function ($user, $ability) {
            return $user->hasRole('admin') ? true : null;
        });
    }
}
