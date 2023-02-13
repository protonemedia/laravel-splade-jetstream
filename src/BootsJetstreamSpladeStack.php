<?php

namespace Laravel\Jetstream;

use Laravel\Fortify\Contracts;
use Laravel\Jetstream\Http\Controllers\Splade\Responses;
use ProtoneMedia\Splade\Facades\Splade;
use ProtoneMedia\Splade\Http\SpladeMiddleware;

trait BootsJetstreamSpladeStack
{
    /**
     * Boot any Splade related services.
     *
     * @return void
     */
    protected function bootSplade()
    {
        $this->app->singleton(Contracts\FailedPasswordConfirmationResponse::class, Responses\FailedPasswordConfirmationResponse::class);
        $this->app->singleton(Contracts\LoginResponse::class, Responses\LoginResponse::class);
        $this->app->singleton(Contracts\LogoutResponse::class, Responses\LogoutResponse::class);
        $this->app->singleton(Contracts\PasswordConfirmedResponse::class, Responses\PasswordConfirmedResponse::class);
        $this->app->singleton(Contracts\PasswordResetResponse::class, Responses\PasswordResetResponse::class);
        $this->app->singleton(Contracts\PasswordUpdateResponse::class, Responses\PasswordUpdateResponse::class);
        $this->app->singleton(Contracts\ProfileInformationUpdatedResponse::class, Responses\ProfileInformationUpdatedResponse::class);
        $this->app->singleton(Contracts\RecoveryCodesGeneratedResponse::class, Responses\RecoveryCodesGeneratedResponse::class);
        $this->app->singleton(Contracts\RegisterResponse::class, Responses\RegisterResponse::class);
        $this->app->singleton(Contracts\SuccessfulPasswordResetLinkRequestResponse::class, Responses\SuccessfulPasswordResetLinkRequestResponse::class);
        $this->app->singleton(Contracts\TwoFactorConfirmedResponse::class, Responses\TwoFactorConfirmedResponse::class);
        $this->app->singleton(Contracts\TwoFactorDisabledResponse::class, Responses\TwoFactorDisabledResponse::class);
        $this->app->singleton(Contracts\TwoFactorEnabledResponse::class, Responses\TwoFactorEnabledResponse::class);
        $this->app->singleton(Contracts\TwoFactorLoginResponse::class, Responses\TwoFactorLoginResponse::class);
        $this->app->singleton(Contracts\VerifyEmailResponse::class, Responses\VerifyEmailResponse::class);

        SpladeMiddleware::afterOriginalResponse(function () {
            if (! session('flash.banner')) {
                return;
            }

            Splade::share('jetstreamBanner', function () {
                return [
                    'banner' => session('flash.banner'),
                    'bannerStyle' => session('flash.bannerStyle'),
                ];
            });
        });
    }
}
