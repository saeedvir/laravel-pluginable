<?php

namespace SaeedVir\LaravelPluginable\Commands;

use Illuminate\Console\Command;
use SaeedVir\LaravelPluginable\Facades\Plugin;

class PluginListCommand extends Command
{
    protected $signature = 'plugin:list';

    protected $description = 'List all discovered plugins and their status';

    public function handle(): int
    {
        $plugins = Plugin::all();

        if (empty($plugins)) {
            $this->warn('No plugins found.');
            return self::SUCCESS;
        }

        $rows = [];
        $source = Plugin::getManifestSource();
        foreach ($plugins as $name => $plugin) {
            $enabled = Plugin::enabled($name);
            $rows[] = [
                $name,
                $plugin['path'],
                $enabled ? '<fg=green>Enabled</>' : '<fg=red>Disabled</>',
                $source
            ];
        }

        $this->table(
            ['Name', 'Path', 'Status', 'Source'],
            $rows
        );

        return self::SUCCESS;
    }
}
