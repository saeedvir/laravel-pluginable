<?php

namespace SaeedVir\LaravelPluginable\Tests\Unit;

use Illuminate\Filesystem\Filesystem;
use SaeedVir\LaravelPluginable\PluginManifest;
use SaeedVir\LaravelPluginable\Tests\TestCase;

class PluginManifestTest extends TestCase
{
    public function test_process_creates_manifest_file()
    {
        $files = new Filesystem;
        $pluginsPath = $this->getTestPluginsPath();
        $manifestPath = __DIR__ . '/../../cache/plugins.php';
        
        // Ensure cache dir exists
        if (!is_dir(dirname($manifestPath))) {
            mkdir(dirname($manifestPath), 0755, true);
        }

        $manifest = new PluginManifest($files, $pluginsPath, $manifestPath);
        
        // Create a dummy plugin
        $this->createTestPlugin('CachedPlugin');
        
        $manifest->process();
        
        $this->assertFileExists($manifestPath);
        
        $content = require $manifestPath;
        $this->assertArrayHasKey('CachedPlugin', $content);
        $this->assertEquals('CachedPlugin', $content['CachedPlugin']['name']);
        
        // Cleanup
        if (file_exists($manifestPath)) {
            unlink($manifestPath);
        }
    }
}
