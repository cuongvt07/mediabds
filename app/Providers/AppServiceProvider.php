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
        View::composer(['site.*', 'components.layouts.site-admin'], function ($view) {
            $phone = '';
            $zalo = '';

            if (Schema::hasTable('site_settings')) {
                $settings = SiteSetting::query()
                    ->whereIn('key', ['contact_phone', 'contact_zalo'])
                    ->pluck('value', 'key');
                $phone = (string) ($settings['contact_phone'] ?? '');
                $zalo = (string) ($settings['contact_zalo'] ?? '');
            }

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
                'zaloHref' => $zaloHref,
            ]);
        });
    }
}
