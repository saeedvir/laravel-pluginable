<?php

namespace SaeedVir\LaravelPluginable\Tests\Unit;

use Illuminate\Support\Facades\Blade;
use SaeedVir\LaravelPluginable\PluginManager;
use SaeedVir\LaravelPluginable\Tests\TestCase;

class BladeHookTest extends TestCase
{
    protected PluginManager $pluginManager;

    protected function setUp(): void
    {
        parent::setUp();
        $this->pluginManager = new PluginManager($this->app);
    }

    public function test_register_and_render_hook_content()
    {
        $this->pluginManager->registerHook('header_start', '<!-- Hook Content -->');
        
        $output = $this->pluginManager->renderHook('header_start');
        
        $this->assertEquals('<!-- Hook Content -->', $output);
    }

    public function test_render_hook_concatenates_multiple_registrations()
    {
        $this->pluginManager->registerHook('footer', 'Part 1');
        $this->pluginManager->registerHook('footer', 'Part 2');

        $output = $this->pluginManager->renderHook('footer');

        $this->assertEquals('Part 1Part 2', $output);
    }

    public function test_render_hook_returns_empty_string_for_unknown_hook()
    {
        $output = $this->pluginManager->renderHook('unknown_hook');

        $this->assertEquals('', $output);
    }

    public function test_blade_directive_plugin_hook()
    {
        // Mock facade call
        $this->pluginManager->registerHook('test_directive', 'Directive Content');
        
        // We can't easily compile blade string in this unit test without full app boot,
        // but we can check if directive callback returns correct PHP code.
        
        $directives = Blade::getCustomDirectives();
        
        $this->assertArrayHasKey('pluginHook', $directives);
        
        $compiler = $directives['pluginHook'];
        $phpCode = $compiler("'test_directive'");
        
        $this->assertEquals("<?php echo \SaeedVir\LaravelPluginable\Facades\Plugin::renderHook('test_directive'); ?>", $phpCode);
    }
}
