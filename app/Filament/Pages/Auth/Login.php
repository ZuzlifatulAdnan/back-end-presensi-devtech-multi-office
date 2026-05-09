<?php

namespace App\Filament\Pages\Auth;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Auth\MultiFactor\Contracts\HasBeforeChallengeHook;
use Filament\Auth\Pages\Login as BaseLogin;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Filament\Schemas\Components\Component;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        // Auto fill credentials in development environment
        if (app()->environment(['local', 'development'])) {
            $this->form->fill([
                'email' => config('app.default_login.email', 'admin@admin.com'),
                'password' => config('app.default_login.password', 'password'),
            ]);
        }
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
            ->default(app()->environment(['local', 'development']) ? config('app.default_login.email', 'admin@admin.com') : null);
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
            ->default(app()->environment(['local', 'development']) ? config('app.default_login.password', 'password') : null);
    }

    public function getHeading(): string|Htmlable
    {
        return 'Sign in to Absensi DevTech';
    }

    public function getSubheading(): string|Htmlable|null
    {
        if (app()->environment(['local', 'development'])) {
            return 'Development mode: Login credentials are pre-filled';
        }

        return 'Welcome back! Please sign in to your account.';
    }

    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        /** @var \Illuminate\Auth\SessionGuard $authGuard */
        $authGuard = Filament::auth();

        $authProvider = $authGuard->getProvider();
        $credentials = $this->getCredentialsFromFormData($data);

        $user = $authProvider->retrieveByCredentials($credentials);

        if ((! $user) || (! $authProvider->validateCredentials($user, $credentials))) {
            $this->userUndertakingMultiFactorAuthentication = null;

            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        if (
            filled($this->userUndertakingMultiFactorAuthentication) &&
            (decrypt($this->userUndertakingMultiFactorAuthentication) === $user->getAuthIdentifier())
        ) {
            $this->multiFactorChallengeForm->validate();
        } else {
            foreach (Filament::getMultiFactorAuthenticationProviders() as $multiFactorAuthenticationProvider) {
                if (! $multiFactorAuthenticationProvider->isEnabled($user)) {
                    continue;
                }

                $this->userUndertakingMultiFactorAuthentication = encrypt($user->getAuthIdentifier());

                if ($multiFactorAuthenticationProvider instanceof HasBeforeChallengeHook) {
                    $multiFactorAuthenticationProvider->beforeChallenge($user);
                }

                break;
            }

            if (filled($this->userUndertakingMultiFactorAuthentication)) {
                $this->multiFactorChallengeForm->fill();

                return null;
            }
        }

        if (($user instanceof FilamentUser) && ! in_array($user->role, ['admin', 'manager'], true)) {
            $this->fireFailedEvent($authGuard, $user, $credentials);

            throw ValidationException::withMessages([
                'email' => 'Hanya admin atau manager yang dapat mengakses dashboard.',
            ]);
        }

        if (! $authGuard->attemptWhen($credentials, function (\Illuminate\Contracts\Auth\Authenticatable $user): bool {
            if (! ($user instanceof FilamentUser)) {
                return true;
            }

            return $user->canAccessPanel(Filament::getCurrentOrDefaultPanel());
        }, $data['remember'] ?? false)) {
            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwFailureValidationException();
        }

        session()->regenerate();

        return app(LoginResponse::class);
    }
}
