<?php

namespace SaeedVir\LaravelPluginable\Tests\Unit;

use SaeedVir\LaravelPluginable\Facades\Plugin;
use SaeedVir\LaravelPluginable\PluginManager;
use SaeedVir\LaravelPluginable\Tests\TestCase;

class FacadeTest extends TestCase
{
    public function test_facade_resolves_manager()
    {
        $this->assertInstanceOf(PluginManager::class, Plugin::getFacadeRoot());
    }

    public function test_facade_methods()
    {
        $manager = $this->mock(PluginManager::class);
        
        $manager->shouldReceive('all')->once()->andReturn([]);
        
        Plugin::swap($manager);
        
        $this->assertEquals([], Plugin::all());
    }
}
