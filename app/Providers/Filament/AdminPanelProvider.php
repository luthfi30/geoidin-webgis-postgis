<?php

namespace App\Providers\Filament;

use Filament\Http\Middleware\Authenticate;
use BezhanSalleh\FilamentShield\FilamentShieldPlugin;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets;
use Filament\Navigation\NavigationItem;
use Filament\Enums\NavigationLayout;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;
use Joaopaulolndev\FilamentEditProfile\FilamentEditProfilePlugin;


class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->brandName('GEODIN GIS') 
            ->font('Inter')
            ->colors([
                'primary' => Color::Sky,
                'gray' => Color::Slate,
                'info' => Color::Blue,
                'success' => Color::Emerald,
                'warning' => Color::Orange,
                'danger' => Color::Rose,
            ])
            // 2. Menambahkan Favicon (Opsional jika Anda punya file-nya)
            // ->favicon(asset('favicon.ico'))
            
            // 3. Konfigurasi Navigasi & Layout
            ->sidebarCollapsibleOnDesktop() // Sidebar bisa diciutkan
            ->maxContentWidth('full') // Dashboard menggunakan lebar penuh layar
            
            // 4. Fitur Global Search (Pencarian di seluruh menu)
            ->globalSearchKeyBindings(['command+k', 'ctrl+k'])
            
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            
            ->widgets([
                // Widgets\AccountWidget::class, // Hapus atau simpan sesuai selera
                // Widgets\FilamentInfoWidget::class, // Widget default Filament
            ])
            
            ->navigationItems([
                NavigationItem::make('Open Map')
                    ->url('/', shouldOpenInNewTab: true)
                    ->icon('heroicon-o-map-pin') // Mengganti ikon map yang lebih spesifik
                    ->sort(2),
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
            ->plugins([
                FilamentShieldPlugin::make(),
                FilamentEditProfilePlugin::make()
                ->setTitle('My Profile')
                ->setNavigationLabel('My Profile')
                ->setNavigationGroup('Group Profile')
                 ->setIcon('heroicon-o-user'),
                
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}