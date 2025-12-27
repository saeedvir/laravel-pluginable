<?php

namespace SaeedVir\LaravelPluginable\Facades;

use Illuminate\Support\Facades\Facade;
use SaeedVir\LaravelPluginable\PluginManager;

/**
 * @method static array all()
 * @method static array|null find(string $name)
 * @method static bool enabled(string $name)
 * @method static void registerHook(string $name, mixed $content)
 * @method static string renderHook(string $name)
 *
 * @see \SaeedVir\LaravelPluginable\PluginManager
 */
class Plugin extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PluginManager::class;
    }
}
