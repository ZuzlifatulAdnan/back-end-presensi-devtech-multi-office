<?php

namespace App\Providers\Filament;

use App\Models\AppSetting;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Throwable;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->registration()
            ->login(\App\Filament\Pages\Auth\Login::class)
            ->brandName(fn (): string => self::branding()?->app_name ?? config('app.name'))
            ->brandLogo(fn (): string => self::branding()?->logoUrl() ?? asset('img/logo.svg'))
            ->darkModeBrandLogo(fn (): string => self::branding()?->logoDarkUrl() ?? asset('img/logo-dark.svg'))
            ->brandLogoHeight('4rem')
            ->favicon(fn (): string => self::branding()?->faviconUrl() ?? asset('favicon.svg'))
            ->colors([
                'primary' => Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\Filament\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\Filament\Pages')
            ->pages([
                \App\Filament\Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\Filament\Widgets')
            ->widgets([
                \App\Filament\Widgets\DashboardStatsWidget::class,
                \App\Filament\Widgets\AttendanceChartWidget::class,
                \App\Filament\Widgets\LatestAttendanceWidget::class,
                \App\Filament\Widgets\PendingApprovalsWidget::class,
                \App\Filament\Widgets\PendingOvertimeWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->renderHook(
                PanelsRenderHook::BODY_END,
                fn (): string => app()->environment(['local', 'development']) ?
                '<script>
                        function fillLoginForm() {
                            const emailInput = document.querySelector(\'input[type="email"]\');
                            const passwordInput = document.querySelector(\'input[type="password"]\');

                            if (emailInput && emailInput.value === \'\') {
                                emailInput.value = \'admin@admin.com\';
                                emailInput.dispatchEvent(new Event(\'input\', { bubbles: true }));
                                emailInput.dispatchEvent(new Event(\'change\', { bubbles: true }));
                            }

                            if (passwordInput && passwordInput.value === \'\') {
                                passwordInput.value = \'password\';
                                passwordInput.dispatchEvent(new Event(\'input\', { bubbles: true }));
                                passwordInput.dispatchEvent(new Event(\'change\', { bubbles: true }));
                            }
                        }

                        document.addEventListener(\'DOMContentLoaded\', function() {
                            setTimeout(fillLoginForm, 100);
                            setTimeout(fillLoginForm, 500);
                            setTimeout(fillLoginForm, 1000);
                        });

                        document.addEventListener(\'livewire:init\', function() {
                            setTimeout(fillLoginForm, 100);
                            setTimeout(fillLoginForm, 500);
                        });

                        window.addEventListener(\'load\', function() {
                            setTimeout(fillLoginForm, 100);
                        });
                    </script>' : ''
            );
    }

    /**
     * Branding row, resolved lazily so the panel still boots before the
     * app_settings table exists (fresh install, migrations, CI).
     */
    protected static function branding(): ?AppSetting
    {
        try {
            return AppSetting::current();
        } catch (Throwable) {
            return null;
        }
    }
}
