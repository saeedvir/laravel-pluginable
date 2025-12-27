<?php

namespace SaeedVir\LaravelPluginable\Tests\Unit;

use SaeedVir\LaravelPluginable\PluginManager;
use SaeedVir\LaravelPluginable\Tests\TestCase;

class MiddlewareTest extends TestCase
{
    protected PluginManager $pluginManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pluginManager = new PluginManager($this->app);
    }

    public function test_register_plugin_middleware_aliases()
    {
        $pluginName = 'TestPlugin';
        $this->createTestPlugin($pluginName);

        $pluginPath = $this->getTestPluginsPath().'/'.$pluginName;
        $middlewarePath = $pluginPath.'/Middleware';
        if (!is_dir($middlewarePath)) mkdir($middlewarePath, 0755, true);

        $middlewareContent = "<?php\n\nnamespace Tests\\Fixtures\\Plugins\\{$pluginName}\\Middleware;\n\nclass CoreAuth\n{\n    public function handle(\$request, \$next)\n    {\n        return \$next(\$request);\n    }\n}";

        file_put_contents($middlewarePath.'/CoreAuth.php', $middlewareContent);

        // Manually require since we don't have composer dump-autoload in test env
        require_once $middlewarePath.'/CoreAuth.php';

        $this->pluginManager->register();
        $this->pluginManager->boot();

        $alias = 'testplugin.coreAuth';
        $expectedClass = "Tests\\Fixtures\\Plugins\\{$pluginName}\\Middleware\\CoreAuth";

        $router = $this->app['router'];
        $middleware = $router->getMiddleware();

        $this->assertArrayHasKey($alias, $middleware);
        $this->assertEquals($expectedClass, $middleware[$alias]);
    }
}
