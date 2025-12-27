<?php

namespace SaeedVir\LaravelPluginable\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use SaeedVir\LaravelPluginable\PluginManifest;

class PluginCacheCommand extends Command
{
    protected $signature = 'plugin:cache';

    protected $description = 'Create a cache file for faster plugin loading';

    public function handle(): int
    {
        $this->call('plugin:clear');

        $pluginsPath = config('laravel-pluginable.plugins_path', app_path('Plugins'));
        $manifestPath = $this->laravel->bootstrapPath('cache/plugins.php');

        $manifest = new PluginManifest(
            new Filesystem,
            $pluginsPath,
            $manifestPath
        );

        $manifest->process();

        $this->info('Plugins cached successfully!');

        return self::SUCCESS;
    }
}
