<?php

namespace SaeedVir\LaravelPluginable\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class PluginClearCommand extends Command
{
    protected $signature = 'plugin:clear';

    protected $description = 'Remove the plugin cache file';

    public function handle(): int
    {
        $files = new Filesystem;
        $manifestPath = $this->laravel->bootstrapPath('cache/plugins.php');

        if ($files->exists($manifestPath)) {
            $files->delete($manifestPath);
            $this->info('Plugin cache cleared!');
        } else {
            $this->info('No plugin cache found.');
        }

        return self::SUCCESS;
    }
}
