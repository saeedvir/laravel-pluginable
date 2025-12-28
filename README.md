# Laravel Pluginable

An extensive Laravel plugin system that provides automatic registration of routes, controllers, services, views, and configurations for modular application development.

![banner](https://raw.githubusercontent.com/saeedvir/laravel-pluginable/refs/heads/main/banner.jpeg?raw=true)

> [!NOTE]  
> Laravel Pluginable Forked From [paramientos/laravel-plugin-system](https://github.com/paramientos/laravel-plugin-system)


## Features

-  **Automatic Plugin Discovery** - Automatically scans and registers plugins
- ️ **Route Registration** - Auto-registers plugin routes with customizable prefixes
-  **Controller Binding** - Automatically binds plugin controllers to the service container
-  **Service Registration** - Registers services as singletons with interface binding support
-  **View Integration** - Seamless integration with Laravel views and Livewire Volt
- ️ **Config Management** - Automatic configuration loading and merging
-  **Plugin Generator** - Artisan command to create new plugins with boilerplate code
-  **Component Generator** - Create commands, controllers, listeners, events, and views within plugins (v1.5)
-  **Component Management** - Add components to existing plugins with duplicate detection (v1.5)
- 🚀 **Performance Cache** - Cache plugin manifest for production speed (v2.0)
- 🛠️ **Plugin Facade** - Easy programmatic access to plugin data (v2.0)
- 🪝 **Blade Hooks** - Inject content into views from plugins (v2.0)
- 🛡️ **Middleware Support** - Auto-register plugin middleware (v2.0)
- 🌍 **Localization & Migrations** - Auto-load translations and database migrations (v2.0)

## Installation

Install the package via Composer:

```bash
composer require saeedvir/laravel-pluginable
```

Publish the configuration file:

```bash
php artisan vendor:publish --tag=laravel-pluginable-config
```

## Key Features (v1.5)

### Smart Plugin Management
- **Existing Plugin Detection**: Automatically detects if a plugin already exists
- **Component Addition**: Add new components to existing plugins without recreation
- **Duplicate Prevention**: Warns and skips if component already exists
- **Flexible Component Creation**: Create individual or multiple components at once

### Supported Components
- **Commands**: Custom Artisan commands with proper signatures
- **Controllers**: RESTful controllers with CRUD operations (extends Laravel Controller)
- **Events**: Broadcastable events with proper structure
- **Listeners**: Queue-enabled listeners with error handling
- **Views**: Blade templates or Livewire Volt components
- **Routes**: RESTful route definitions with proper namespacing (v1.5)
- **Enums**: PHP 8.1+ enums with string backing type
- **Traits**: Reusable trait classes (concerns) for shared functionality
- **Providers**: Service providers automatically created for each plugin

## Configuration

The configuration file `config/laravel-pluginable.php` allows you to customize:

```php
return [
    // Path where plugins are stored
    'plugins_path' => app_path('Plugins'),
    
    // Base namespace for plugins
    'plugin_namespace' => 'App\\Plugins',
    
    // Whether to prefix routes with 'plugins/'
    'use_plugins_prefix_in_routes' => false,
    
    // Default view type for new plugins
    'default_view_type' => 'volt', // 'volt' or 'blade'
    
    // Enable/disable Volt support
    'enable_volt_support' => true,
];
```

## Usage

### Creating a Plugin

Use the Artisan command to create a new plugin:

```bash
# Create plugin with default view type (configured in config)
php artisan make:plugin MyAwesomePlugin

# Create plugin with Volt views
php artisan make:plugin MyAwesomePlugin --view-type=volt

# Create plugin with traditional Blade views
php artisan make:plugin MyAwesomePlugin --view-type=blade

# Auto-detect best view type based on configuration and availability
php artisan make:plugin MyAwesomePlugin --view-type=auto
```

### Creating Plugin Components (v1.5)

Generate specific components within your plugin:

```bash
# Create a command within the plugin
php artisan make:plugin MyAwesomePlugin --command=ProcessDataCommand

# Create a controller within the plugin
php artisan make:plugin MyAwesomePlugin --controller=ApiController

# Create an event within the plugin
php artisan make:plugin MyAwesomePlugin --event=DataProcessedEvent

# Create a listener within the plugin
php artisan make:plugin MyAwesomePlugin --listener=SendNotificationListener

# Create a view within the plugin
php artisan make:plugin MyAwesomePlugin --view=dashboard

# Create routes for the plugin (v1.5)
php artisan make:plugin MyAwesomePlugin --route

# Create an enum within the plugin
php artisan make:plugin MyAwesomePlugin --enum=Status

# Create a trait within the plugin
php artisan make:plugin MyAwesomePlugin --trait=Cacheable

# Create a middleware within the plugin
php artisan make:plugin MyAwesomePlugin --middleware=AdminMiddleware

# Create a language file within the plugin
php artisan make:plugin MyAwesomePlugin --lang=messages

# Combine multiple components
php artisan make:plugin MyAwesomePlugin --command=ProcessCommand --controller=ProcessController --event=ProcessedEvent --enum=Status --trait=Cacheable --middleware=AdminMiddleware --lang=messages --route
```

### Adding Components to Existing Plugins (v1.5)

You can add new components to existing plugins without recreating them:

```bash
# Add a command to existing plugin
php artisan make:plugin ExistingPlugin --command=NewCommand

# Add multiple components to existing plugin
php artisan make:plugin ExistingPlugin --controller=ApiController --event=UserRegistered --enum=Status --trait=Cacheable --middleware=AdminMiddleware --lang=messages --route

# If component already exists, it will be skipped with a warning
php artisan make:plugin ExistingPlugin --command=ExistingCommand
# Output: Command file 'ExistingCommand.php' already exists in plugin 'ExistingPlugin'. Skipping...
```

### Listing Plugins

Display all discovered plugins and their current status:

```bash
php artisan plugin:list
```

This creates the following structure:

```
app/Plugins/MyAwesomePlugin/
├── config.php                          # Plugin configuration
├── routes.php                          # Plugin routes
├── MyAwesomePluginProvider.php         # Service provider (auto-created)
├── Controllers/
│   └── MyAwesomePluginController.php   # Plugin controller
├── Services/
│   ├── MyAwesomePluginService.php      # Plugin service
│   └── MyAwesomePluginServiceInterface.php # Service interface
├── Middleware/                         # Generated middleware
│   └── AdminMiddleware.php
├── Lang/                               # Generated language files
│   └── messages.php
├── Commands/                           # Generated commands (v1.5)
│   └── ProcessDataCommand.php
├── Events/                             # Generated events (v1.5)
│   └── DataProcessedEvent.php
├── Listeners/                          # Generated listeners (v1.5)
│   └── SendNotificationListener.php
├── Status.php                          # Generated enum
├── Cacheable.php                       # Generated trait
└── Views/
    └── index.blade.php                 # Livewire Volt component or Blade view
```

### Plugin Structure

#### Config File (`config.php`)
```php
<?php
return [
    'name' => 'MyAwesomePlugin',
    'version' => '1.0.0',
    'description' => 'MyAwesomePlugin plugin',
    'enabled' => true,
];
```

#### Routes File (`routes.php`)
```php
<?php
use Livewire\Volt\Volt;

Volt::route('/', 'index');
```

#### Controller
```php
<?php
namespace App\Plugins\MyAwesomePlugin\Controllers;

use App\Http\Controllers\Controller;

class MyAwesomePluginController extends Controller
{
    public function index()
    {
        // Controller logic
    }
}
```

#### Service & Interface
```php
<?php
namespace App\Plugins\MyAwesomePlugin\Services;

interface MyAwesomePluginServiceInterface
{
    public function handle(): array;
}

class MyAwesomePluginService implements MyAwesomePluginServiceInterface
{
    public function handle(): array
    {
        return [
            'message' => 'MyAwesomePlugin service is working!',
            'timestamp' => now()->toISOString(),
        ];
    }
}
```

#### Service Provider
```php
<?php
namespace App\Plugins\MyAwesomePlugin;

use Illuminate\Support\ServiceProvider;

class MyAwesomePluginProvider extends ServiceProvider
{
    public function register(): void
    {
        // Register plugin services here
    }

    public function boot(): void
    {
        // Boot plugin services here
    }
}
```

#### Enum
```php
<?php
namespace App\Plugins\MyAwesomePlugin;

enum Status: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';
    case PENDING = 'pending';
}
```

#### Trait
```php
<?php
namespace App\Plugins\MyAwesomePlugin;

trait Cacheable
{
    protected int $cacheTimeout = 3600;

    public function getCacheKey(string $suffix = ''): string
    {
        return static::class . ($suffix ? ":{$suffix}" : '');
    }

    public function clearCache(string $suffix = ''): void
    {
        cache()->forget($this->getCacheKey($suffix));
    }
}
```

#### Views

The plugin system supports both **Livewire Volt** and **traditional Blade** views:

**Volt Component (default):**
```php
<?php
new class extends \Livewire\Volt\Component
{
    public string $message = 'Welcome to MyAwesomePlugin Plugin!';

    public function mount(): void
    {
        $this->message = 'Hello from MyAwesomePlugin!';
    }
}
?>

<div class="p-6 bg-white rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">MyAwesomePlugin Plugin</h1>
    <p class="text-gray-600 mb-4">{{ $message }}</p>
</div>
```

**Traditional Blade View:**
```blade
@extends('layouts.app')

@section('content')
<div class="p-6 bg-white rounded-lg shadow-md">
    <h1 class="text-2xl font-bold text-gray-800 mb-4">MyAwesomePlugin Plugin</h1>
    <p class="text-gray-600 mb-4">Welcome to MyAwesomePlugin Plugin!</p>
    
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h2 class="text-lg font-semibold text-blue-800 mb-2">Plugin Information</h2>
        <ul class="text-blue-700 space-y-1">
            <li><strong>View Type:</strong> Traditional Blade</li>
        </ul>
    </div>
</div>
@endsection
```

### Accessing Plugins

Once created, your plugin will be automatically registered and accessible via:

- **Routes**: `http://your-app.com/myawesomeplugin/` (or `/plugins/myawesomeplugin/` if prefix is enabled)
- **Config**: `config('MyAwesomePlugin.name')`
- **Services**: Injected via dependency injection or `app(MyAwesomePluginServiceInterface::class)`

### Service Injection

Plugin services are automatically registered and can be injected:

```php
class SomeController extends Controller
{
    public function __construct(
        private MyAwesomePluginServiceInterface $pluginService
    ) {}

    public function index()
    {
        $result = $this->pluginService->handle();
        return response()->json($result);
    }
}
```

## Advanced Configuration

### Custom Plugin Path

You can change the default plugin path in the configuration:

```php
'plugins_path' => base_path('custom/plugins'),
```

### Custom Namespace

Change the base namespace for plugins:

```php
'plugin_namespace' => 'Custom\\Plugins',
```

### Route Prefixing

Enable route prefixing to add 'plugins/' to all plugin routes:

```php
'use_plugins_prefix_in_routes' => true,
```

## Advanced Features (v2.0)

### Performance Optimization

In production, you should cache the plugin manifest to avoid filesystem scans on every request.

```bash
# detailed
php artisan plugin:cache
```

To clear the cache:
```bash
php artisan plugin:clear
```

### Plugin Facade

The `Plugin` facade provides a convenient way to interact with the plugin system.

```php
use SaeedVir\LaravelPluginable\Facades\Plugin;

// Get all plugins
$plugins = Plugin::all();

// Find a specific plugin
$myPlugin = Plugin::find('MyAwesomePlugin');

// Check if a plugin is enabled
if (Plugin::enabled('MyAwesomePlugin')) {
    // ...
}
```

### Middleware Support

You can define middleware in your plugin's `Middleware` directory. They are automatically aliased as `pluginname.middlewarename`.

**Example:**
`Plugins/MyPlugin/Middleware/AdminCheck.php`

```php
// In your route file
Route::get('/admin', 'AdminController@index')
    ->middleware('myplugin.adminCheck');
```

### Blade Hooks

Plugins can inject content into your application's views using Blade Hooks.

**1. Define the hook in your layout:**
```blade
<!-- resources/views/layouts/app.blade.php -->
<body>
    @pluginHook('body_start')
    
    @yield('content')
</body>
```

**2. Register content from your plugin:**
```php
// Plugins/MyPlugin/MyPluginProvider.php
use SaeedVir\LaravelPluginable\Facades\Plugin;

public function boot()
{
    Plugin::registerHook('body_start', view('myplugin::banner'));
    // or
    Plugin::registerHook('body_start', '<div class="alert">Warning!</div>');
}
```

### Migrations & Translations

- **Migrations**: Place your migrations in `Plugins/MyPlugin/database/migrations`. They will be automatically registered.
- **Translations**: Place your language files in `Plugins/MyPlugin/lang`. You can access them via `trans('MyPlugin::file.key')`.

## Requirements

- PHP 8.1+
- Laravel 10.0+ or 11.0+
- Livewire Volt 1.0+ (for view components)

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Contributing

Contributions are welcome! Please feel free to submit a Pull Request.

## Support

If you discover any security vulnerabilities or bugs, please send an e-mail to saeed.es91@gmail.com
