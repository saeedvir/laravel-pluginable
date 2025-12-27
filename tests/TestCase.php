<?php

namespace SaeedVir\LaravelPluginable\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use SaeedVir\LaravelPluginable\LaravelPluginableServiceProvider;

class TestCase extends Orchestra
{
    protected function getPackageProviders($app)
    {
        return [
            LaravelPluginableServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app)
    {
        // Setup default config
        $app['config']->set('laravel-pluginable.plugins_path', $this->getTestPluginsPath());
        $app['config']->set('laravel-pluginable.plugin_namespace', 'Tests\\Fixtures\\Plugins');
    }

    protected function tearDown(): void
    {
        $this->deleteDirectory($this->getTestPluginsPath());
        parent::tearDown();
    }

    protected function deleteDirectory($dir)
    {
        if (!file_exists($dir)) {
            return true;
        }

        if (!is_dir($dir)) {
            return @unlink($dir);
        }

        foreach (scandir($dir) as $item) {
            if ($item == '.' || $item == '..') {
                continue;
            }

            if (!$this->deleteDirectory($dir . DIRECTORY_SEPARATOR . $item)) {
                return false;
            }
        }

        return @rmdir($dir);
    }

    protected function getTestPluginsPath(): string
    {
        return __DIR__ . '/fixtures/Plugins';
    }
    
    // Helper to create test plugins, copied from what likely was there or needed
    protected function createTestPlugin(string $name, array $config = []): void
    {
        $path = $this->getTestPluginsPath() . '/' . $name;
        if (!is_dir($path)) {
            mkdir($path, 0755, true);
        }
        
        $configFile = $path . '/config.php';
        $configContent = '<?php return ' . var_export(array_merge([
            'name' => $name,
            'enabled' => true,
        ], $config), true) . ';';
        
        file_put_contents($configFile, $configContent);
    }

    protected function createTestPluginWithRoutes(string $name): void
    {
        $this->createTestPlugin($name);
        $path = $this->getTestPluginsPath() . '/' . $name;
        file_put_contents($path . '/routes.php', '<?php use Illuminate\Support\Facades\Route; Route::get("/", function() { return "Hello"; });');
    }
}
