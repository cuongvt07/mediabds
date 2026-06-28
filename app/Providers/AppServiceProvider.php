<?php

namespace App\Providers;

use App\Models\RealEstateListing;
use App\Models\SiteSetting;
use App\Policies\ListingPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        Gate::policy(RealEstateListing::class, ListingPolicy::class);

        // Chia sẻ thông tin liên hệ (config bên admin) cho mọi view của site nhà trọ.
        View::composer(['site.*', 'livewire.user.*', 'components.layouts.site-admin'], function ($view) {
            $phone = '';
            $zalo = '';
            $email = '';
            $facebook = '';
            $position = 'right';
            $showListingTime = true;

            if (Schema::hasTable('site_settings')) {
                $settings = SiteSetting::query()
                    ->whereIn('key', ['contact_phone', 'contact_zalo', 'contact_email', 'contact_facebook', 'contact_position', 'show_listing_time'])
                    ->pluck('value', 'key');
                $phone = (string) ($settings['contact_phone'] ?? '');
                $zalo = (string) ($settings['contact_zalo'] ?? '');
                $email = (string) ($settings['contact_email'] ?? '');
                $facebook = (string) ($settings['contact_facebook'] ?? '');
                $position = ($settings['contact_position'] ?? 'right') ?: 'right';
                $showListingTime = ($settings['show_listing_time'] ?? '1') !== '0';
            }

            $view->with('showListingTime', $showListingTime);

            $zaloHref = function (?string $fallbackPhone = null) use ($zalo) {
                $value = trim($zalo) ?: (string) $fallbackPhone;
                if ($value === '') {
                    return null;
                }
                if (str_starts_with($value, 'http')) {
                    return $value;
                }
                return 'https://zalo.me/' . preg_replace('/\D+/', '', $value);
            };

            $view->with('siteContact', [
                'phone' => $phone,
                'zalo' => $zalo,
                'email' => $email,
                'facebook' => $facebook,
                'position' => $position,
                'zaloHref' => $zaloHref,
            ]);
        });
    }
}
