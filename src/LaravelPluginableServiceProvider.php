<?php

namespace SaeedVir\LaravelPluginable;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\ServiceProvider;

class LaravelPluginableServiceProvider extends ServiceProvider
{
    /**
     * @throws BindingResolutionException
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/laravel-pluginable.php',
            'laravel-pluginable'
        );

        $this->app->singleton(PluginManager::class);

        $pluginManager = $this->app->make(PluginManager::class);
        $pluginManager->register();

        $this->commands($pluginManager->getRegisteredCommands());
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/laravel-pluginable.php' => config_path('laravel-pluginable.php'),
        ], 'laravel-pluginable-config');

        $pluginManager = $this->app->make(PluginManager::class);
        $pluginManager->boot();

        \Illuminate\Support\Facades\Blade::directive('pluginHook', function ($expression) {
            return "<?php echo \SaeedVir\LaravelPluginable\Facades\Plugin::renderHook($expression); ?>";
        });
    }
}
