<?php

namespace WalkerChiu\API;

use Illuminate\Support\ServiceProvider;

class APIServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
           __DIR__ .'/config/api.php' => config_path('wk-api.php'),
        ], 'config');

        // Publish migration files
        $from = __DIR__ .'/database/migrations/';
        $to   = database_path('migrations') .'/';
        $this->publishes([
            $from .'create_wk_api_table.php'
                => $to .date('Y_m_d_His', time()) .'_create_wk_api_table.php'
        ], 'migrations');

        $this->loadTranslationsFrom(__DIR__.'/translations', 'php-api');
        $this->publishes([
            __DIR__.'/translations' => resource_path('lang/vendor/php-api'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                config('wk-api.command.cleaner')
            ]);
        }

        config('wk-core.class.api.setting')::observe(config('wk-core.class.api.settingObserver'));
        config('wk-core.class.api.settingLang')::observe(config('wk-core.class.api.settingLangObserver'));
    }

    /**
     * Register the blade directives
     *
     * @return void
     */
    private function bladeDirectives()
    {
    }

    /**
     * Merges user's and package's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        if (!config()->has('wk-api')) {
            $this->mergeConfigFrom(
                __DIR__ .'/config/api.php', 'wk-api'
            );
        }

        $this->mergeConfigFrom(
            __DIR__ .'/config/api.php', 'api'
        );
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param String  $path
     * @param String  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (
            !(
                $this->app instanceof CachesConfiguration
                && $this->app->configurationIsCached()
            )
        ) {
            $config = $this->app->make('config');
            $content = $config->get($key, []);

            $config->set($key, array_merge(
                require $path, $content
            ));
        }
    }
}
