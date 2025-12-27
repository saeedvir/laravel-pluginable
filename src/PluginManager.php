<?php

namespace SaeedVir\LaravelPluginable;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;

class PluginManager
{
    public static bool $usePluginsPrefixInRoutes = false;

    private string $ds = DIRECTORY_SEPARATOR;

    protected string $pluginsPath;
    
    protected string $manifestPath;

    protected Application $app;
    
    protected array $manifest = [];

    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->pluginsPath = config('laravel-pluginable.plugins_path', app_path('Plugins'));
        $this->manifestPath = $app->bootstrapPath('cache/plugins.php');
        self::$usePluginsPrefixInRoutes = config('laravel-pluginable.use_plugins_prefix_in_routes', false);
    }

    public function register(): void
    {
        $this->loadManifest();
        $this->registerPluginConfigs();
        $this->registerPluginServices();
        $this->registerPluginProviders();
    }

    public function boot(): void
    {
        $this->registerPluginViews();
        $this->registerPluginTranslations();
        $this->registerPluginRoutes();
        $this->registerPluginControllers();
        $this->registerPluginMiddleware();
        $this->registerPluginCommands();
        $this->registerPluginEvents();
        $this->registerPluginMigrations();
    }
    
    protected function loadManifest(): void
    {
        if (File::exists($this->manifestPath)) {
            $this->manifest = require $this->manifestPath;
            return;
        }

        // If no cache, scan manually (fallback or dev mode)
        // Or we could build the manifest in memory on the fly using the PluginManifest class logic
        // For simplicity in this refactor, if cache doesn't exist, we'll scan locally 
        // effectively doing what the PluginManifest does but in-memory.
        // However, to keep it DRY, let's use PluginManifest to generate it in memory if needed
        // but not write it unless commanded.
        
        // Actually, for performance, if we are NOT using cache, we just fall back to scanning 
        // as we go (lazy loading) OR we scan everything once into memory.
        // Let's scan once into memory to unification.
        
        $manifestBuilder = new PluginManifest(new Filesystem, $this->pluginsPath, $this->manifestPath);
        
        // We can expose a method on PluginManifest to just 'scan' without writing.
        // But since PluginManifest::process writes, we might need a little duplication or change.
        // For now, let's stick to the previous dynamic scanning logic *inside* this class
        // if the manifest is missing, BUT populate $this->manifest so subsequent calls use it.
        
        if (File::exists($this->pluginsPath)) {
            $pluginDirectories = File::directories($this->pluginsPath);
            foreach ($pluginDirectories as $pluginDir) {
                $pluginName = basename($pluginDir);
                $this->manifest[$pluginName] = [
                     'name' => $pluginName,
                     'path' => $pluginDir,
                     // We will scan lazily or just check files as we iterate if not cached.
                     // To properly support the 'Unified' approach, we should fill this structure.
                     // But for backward compatibility and robustness when cache is missing:
                     'components' => $this->scanPluginComponents($pluginDir, $pluginName)
                ];
            }
        }
    }
    
    protected function scanPluginComponents(string $path, string $name): array 
    {
         // Quick scan helper to mimic manifest structure when cache is missing
         return [
                'commands' => $this->scanDirectory($path . '/Commands'),
                'controllers' => $this->scanDirectory($path . '/Controllers'),
                'events' => $this->scanDirectory($path . '/Events'),
                'listeners' => $this->scanDirectory($path . '/Listeners'),
                'middleware' => $this->scanDirectory($path . '/Middleware'),
                'services' => $this->scanDirectory($path . '/Services'),
                'migrations' => File::exists($path . '/database/migrations') ? $path . '/database/migrations' : null,
                'provider' => File::exists($path . "/{$name}Provider.php"),
                'config' => File::exists($path . '/config.php'),
                'routes' => File::exists($path . '/routes.php'),
                'views' => File::exists($path . '/Views') ? $path . '/Views' : null,
                'lang' => File::exists($path . '/lang') ? $path . '/lang' : null,
         ];
    }
    
    protected function scanDirectory(string $path): array
    {
        if (!File::exists($path)) {
            return [];
        }
        $files = File::files($path);
        return array_map(function ($file) {
            return $file->getFilenameWithoutExtension();
        }, array_filter($files, function ($file) {
            return $file->getExtension() === 'php';
        }));
    }

    /* Facade Helpers */
    
    public function all(): array
    {
        return $this->manifest; 
    }
    
    public function find(string $name): ?array
    {
        return $this->manifest[$name] ?? null;
    }
    
    public function enabled(string $name): bool
    {
        return $this->find($name) && $this->isPluginEnabled($name);
    }
    
    /* Registration Methods */
    
    public function getRegisteredCommands(): array
    {
        return [
            Commands\MakePluginCommand::class,
            Commands\PluginCacheCommand::class,
            Commands\PluginClearCommand::class,
        ];
    }

    protected function registerPluginCommands(): void
    {
        foreach ($this->manifest as $pluginName => $plugin) {
            if (!$this->isPluginEnabled($pluginName)) {
                continue;
            }

            foreach ($plugin['components']['commands'] as $commandName) {
                $namespace = $this->getPluginNamespace($pluginName) . "\\Commands\\{$commandName}";
                if (class_exists($namespace)) {
                    $commandInstance = $this->app->make($namespace);
                    $this->app['Illuminate\Contracts\Console\Kernel']->registerCommand($commandInstance);
                }
            }
        }
    }

    protected function registerPluginEvents(): void
    {
        foreach ($this->manifest as $pluginName => $plugin) {
            if (!$this->isPluginEnabled($pluginName)) {
                continue;
            }

            $events = $plugin['components']['events'];
            $listeners = $plugin['components']['listeners'];

            foreach ($events as $eventName) {
                $eventNamespace = $this->getPluginNamespace($pluginName) . "\\Events\\{$eventName}";
                
                // Naive auto-discovery: Listeners must be in the same plugin
                // and we don't have a mapping in the manifest, only lists.
                // The original code tried every listener against every event.
                // We will keep that logic for compatibility.
                
                foreach ($listeners as $listenerName) {
                    $listenerNamespace = $this->getPluginNamespace($pluginName) . "\\Listeners\\{$listenerName}";
                    if (class_exists($eventNamespace) && class_exists($listenerNamespace)) {
                        Event::listen($eventNamespace, $listenerNamespace);
                    }
                }
            }
        }
    }

    protected function registerPluginServices(): void
    {
        foreach ($this->manifest as $pluginName => $plugin) {
            if (!$this->isPluginEnabled($pluginName)) {
                continue;
            }

            foreach ($plugin['components']['services'] as $serviceName) {
                 $namespace = $this->getPluginNamespace($pluginName) . "\\Services\\{$serviceName}";

                 if (class_exists($namespace)) {
                     $this->app->singleton($namespace, $namespace);
                     $interfaceName = $this->getPluginNamespace($pluginName) . "\\Services\\{$serviceName}Interface";

                     if (interface_exists($interfaceName)) {
                         $this->app->bind($interfaceName, $namespace);
                     }
                 }
            }
        }
    }

    protected function registerPluginViews(): void
    {
        foreach ($this->manifest as $pluginName => $plugin) {
            if (!$this->isPluginEnabled($pluginName)) {
                continue;
            }
            
            $viewsPath = $plugin['components']['views'];

            if ($viewsPath) {
                View::addLocation($viewsPath);
                View::addNamespace("{$pluginName}", $viewsPath);

                if (config('laravel-pluginable.enable_volt_support', true) && class_exists('Livewire\\Volt\\Volt')) {
                    call_user_func(['Livewire\\Volt\\Volt', 'mount'], $viewsPath);
                }
            }
        }
    }
    
    protected function registerPluginTranslations(): void
    {
        foreach ($this->manifest as $pluginName => $plugin) {
            if (!$this->isPluginEnabled($pluginName)) {
                continue;
            }
            
            $langPath = $plugin['components']['lang'];
            
            if ($langPath) {
                $this->app['translator']->addNamespace($pluginName, $langPath);
            }
        }
    }
    
    protected function registerPluginMigrations(): void
    {
        foreach ($this->manifest as $pluginName => $plugin) {
            if (!$this->isPluginEnabled($pluginName)) {
                continue;
            }
            
            $migrationPath = $plugin['components']['migrations'];
            
            if ($migrationPath) {
                $this->app['migrator']->path($migrationPath);
            }
        }
    }

    protected function isPluginEnabled(string $pluginName): bool
    {
        return (bool)config("{$pluginName}.enabled");
    }

    protected function registerPluginConfigs(): void
    {
        foreach ($this->manifest as $pluginName => $plugin) {
            // Configs must be loaded BEFORE checking enabled status because 
            // the enabled status might be IN the config!
            // Wait, the original code checks enabled... but where does it get it from?
            // "config("{$pluginName}.enabled")"
            // If the config file IS the source of truth, we must load it first.
            
            if ($plugin['components']['config']) {
                $configFile = $plugin['path'] . '/config.php';
                 if (File::exists($configFile)) {
                    $config = require $configFile;
                    if (is_array($config)) {
                        config(["{$pluginName}" => $config]);
                    }
                }
            }
        }
    }

    protected function registerPluginRoutes(): void
    {
        foreach ($this->manifest as $pluginName => $plugin) {
            if (!$this->isPluginEnabled($pluginName)) {
                continue;
            }

            if ($plugin['components']['routes']) {
                $routesFile = $plugin['path'] . '/routes.php';
                $pluginNameLower = strtolower($pluginName);

                $prefix = self::$usePluginsPrefixInRoutes
                    ? "plugins/{$pluginNameLower}"
                    : $pluginNameLower;

                Route::prefix($prefix)
                    ->name("plugins.{$pluginNameLower}.")
                    ->group($routesFile);
            }
        }
    }

    protected function registerPluginControllers(): void
    {
        foreach ($this->manifest as $pluginName => $plugin) {
            if (!$this->isPluginEnabled($pluginName)) {
                continue;
            }

            foreach ($plugin['components']['controllers'] as $controllerName) {
                 $namespace = $this->getPluginNamespace($pluginName) . "\\Controllers\\{$controllerName}";
                 if (class_exists($namespace)) {
                     $this->app->bind($namespace, $namespace);
                 }
            }
        }
    }

    protected function registerPluginProviders(): void
    {
        foreach ($this->manifest as $pluginName => $plugin) {
            if (!$this->isPluginEnabled($pluginName)) {
                continue;
            }

            if ($plugin['components']['provider']) {
                $namespace = $this->getPluginNamespace($pluginName) . '\\' . $pluginName . 'Provider';
                if (class_exists($namespace)) {
                    $this->app->register($namespace);
                }
            }
        }
    }

    protected function registerPluginMiddleware(): void
    {
        foreach ($this->manifest as $pluginName => $plugin) {
            if (!$this->isPluginEnabled($pluginName)) {
                continue;
            }

            foreach ($plugin['components']['middleware'] as $middlewareName) {
                $namespace = $this->getPluginNamespace($pluginName) . "\\Middleware\\{$middlewareName}";
                
                if (class_exists($namespace)) {
                    // Alias: plugin.middlewareName (e.g., MyPlugin.AdminAuth)
                    $alias = strtolower($pluginName) . '.' . lcfirst($middlewareName);
                    $this->app['router']->aliasMiddleware($alias, $namespace);
                }
            }
        }
    }
    
    /* Hooks System */
    
    protected array $hooks = [];

    public function registerHook(string $name, mixed $content): void
    {
        $this->hooks[$name][] = $content;
    }

    public function renderHook(string $name): string
    {
        if (!isset($this->hooks[$name])) {
            return '';
        }

        $output = '';
        foreach ($this->hooks[$name] as $content) {
            if ($content instanceof \Illuminate\Contracts\View\View) {
                $output .= $content->render();
            } elseif (is_callable($content)) {
                $output .= call_user_func($content);
            } else {
                $output .= (string) $content;
            }
        }

        return $output;
    }

    protected function getPluginNamespace(string $pluginName): string
    {
        return config('laravel-pluginable.plugin_namespace', 'App\\Plugins') . "\\{$pluginName}";
    }
}
