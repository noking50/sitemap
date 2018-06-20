<?php

namespace Noking50\Sitemap;

use Illuminate\Support\ServiceProvider;

class SitemapServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = true;

    public function boot() {
        $this->publishes([
            __DIR__ . '/../config/sitemap.php' => config_path('sitemap.php'),
        ]);
    }
    
    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register() {
        $this->app->singleton('sitemap', function () {
            return new Sitemap;
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides() {
        return ['sitemap'];
    }

}
