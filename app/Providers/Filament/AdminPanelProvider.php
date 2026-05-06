<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\View\PanelsRenderHook;
use Filament\Widgets;
use Illuminate\Support\HtmlString;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => Color::Amber,
            ])
            ->renderHook(
                PanelsRenderHook::HEAD_END,
                fn (): HtmlString => new HtmlString(<<<'HTML'
<style>
    :root {
        --rwh-bg-primary: #020617;
        --rwh-bg-panel: #0f172a;
        --rwh-bg-panel-soft: #1e293b;
        --rwh-border: #334155;
        --rwh-text-primary: #f1f5f9;
        --rwh-text-secondary: #cbd5e1;
        --rwh-text-muted: #94a3b8;
        --rwh-accent-primary: #fbbf24;
        --rwh-accent-secondary: #38bdf8;
        --rwh-accent-danger: #f97316;
    }

    .fi-body,
    .fi-layout,
    .fi-simple-layout {
        background: var(--rwh-bg-primary) !important;
        color: var(--rwh-text-primary) !important;
    }

    .fi-topbar,
    .fi-sidebar,
    .fi-dropdown-panel,
    .fi-modal-window {
        background: var(--rwh-bg-panel) !important;
        border-color: var(--rwh-border) !important;
    }

    .fi-main,
    .fi-page,
    .fi-section,
    .fi-ta-ctn,
    .fi-in-entry-item,
    .fi-input-wrp {
        background: var(--rwh-bg-panel) !important;
        border-color: var(--rwh-border) !important;
        color: var(--rwh-text-primary) !important;
    }

    .fi-section-content,
    .fi-ta-content,
    .fi-fo-field-wrp {
        background: var(--rwh-bg-panel-soft) !important;
        color: var(--rwh-text-primary) !important;
    }

    .fi-ta-table thead tr,
    .fi-ta-header-cell {
        background: #152544 !important;
        color: #f8fafc !important;
    }

    .fi-ta-row,
    .fi-ta-record {
        background: #22324a !important;
        color: #f1f5f9 !important;
    }

    .fi-ta-row:hover,
    .fi-ta-record:hover {
        background: #2b3f5c !important;
    }

    .fi-btn-color-primary {
        background: var(--rwh-accent-primary) !important;
        color: #020617 !important;
    }

    .fi-btn-color-primary:hover {
        filter: brightness(1.06);
    }

    .fi-link,
    .fi-ta-text-item-label,
    .fi-tabs-item {
        color: var(--rwh-text-secondary) !important;
    }

    .fi-link:hover,
    .fi-tabs-item.fi-active {
        color: var(--rwh-accent-secondary) !important;
    }

    .fi-badge-color-success {
        background: rgba(34, 197, 94, 0.18) !important;
        color: #86efac !important;
    }

    .fi-badge-color-warning {
        background: rgba(251, 191, 36, 0.18) !important;
        color: #fde68a !important;
    }

    .fi-badge-color-danger {
        background: rgba(249, 115, 22, 0.18) !important;
        color: #fdba74 !important;
    }

    .fi-input,
    .fi-select-input,
    .fi-textarea,
    .fi-checkbox-input,
    .fi-radio-input {
        background: #0b1427 !important;
        border-color: var(--rwh-border) !important;
        color: var(--rwh-text-primary) !important;
    }

    .fi-input::placeholder,
    .fi-select-input::placeholder,
    .fi-textarea::placeholder {
        color: var(--rwh-text-muted) !important;
    }

    .fi-header-heading,
    .fi-breadcrumbs-item-label,
    .fi-topbar-item-label,
    .fi-sidebar-item-label,
    .fi-ta-text,
    .fi-ta-header-cell-label,
    .fi-ta-header-cell-label *,
    .fi-ta-header-cell button,
    .fi-ta-header-cell button *,
    .fi-ta-summary,
    .fi-pagination-item-label {
        color: var(--rwh-text-primary) !important;
    }

    .fi-breadcrumbs-item-separator,
    .fi-ta-empty-state-description,
    .fi-pagination-overview {
        color: var(--rwh-text-secondary) !important;
    }
</style>
HTML)
            )
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
                Widgets\FilamentInfoWidget::class,
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
            ]);
    }
}
