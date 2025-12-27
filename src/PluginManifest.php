<?php

namespace SaeedVir\LaravelPluginable;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\PackageManifest;

class PluginManifest
{
    public function __construct(
        public Filesystem $files,
        public string $basePath,
        public string $manifestPath
    ) {
    }

    public function process(): void
    {
        $plugins = [];

        if ($this->files->exists($this->basePath)) {
            $pluginDirectories = $this->files->directories($this->basePath);

            foreach ($pluginDirectories as $pluginDir) {
                $pluginName = basename($pluginDir);
                // We'll store basic info. Enabled status is checked at runtime via config
                // to allow dynamic enabling/disabling without rebuilding cache.
                // However, if we want to cache EVERYTHING, we might burn it in.
                // Let's store available components to avoid disk scans.

                $plugins[$pluginName] = $this->scanPlugin($pluginDir, $pluginName);
            }
        }

        $this->write($plugins);
    }

    protected function scanPlugin(string $path, string $name): array
    {
        return [
            'name' => $name,
            'path' => $path,
            'components' => [
                'commands' => $this->scanDirectory($path . '/Commands'),
                'controllers' => $this->scanDirectory($path . '/Controllers'),
                'events' => $this->scanDirectory($path . '/Events'),
                'listeners' => $this->scanDirectory($path . '/Listeners'),
                'middleware' => $this->scanDirectory($path . '/Middleware'),
                'services' => $this->scanDirectory($path . '/Services'),
                'migrations' => $this->files->exists($path . '/database/migrations') ? $path . '/database/migrations' : null,
                'provider' => $this->files->exists($path . "/{$name}Provider.php"),
                'config' => $this->files->exists($path . '/config.php'),
                'routes' => $this->files->exists($path . '/routes.php'),
                'views' => $this->files->exists($path . '/Views') ? $path . '/Views' : null,
                'lang' => $this->files->exists($path . '/lang') ? $path . '/lang' : null,
            ],
        ];
    }

    protected function scanDirectory(string $path): array
    {
        if (!$this->files->exists($path)) {
            return [];
        }

        $files = $this->files->files($path);
        
        return array_map(function ($file) {
            return $file->getFilenameWithoutExtension();
        }, array_filter($files, function ($file) {
            return $file->getExtension() === 'php';
        }));
    }

    public function write(array $manifest): void
    {
        if (! is_writable(dirname($this->manifestPath))) {
            throw new \Exception('The ' . dirname($this->manifestPath) . ' directory must be present and writable.');
        }

        $this->files->put(
            $this->manifestPath, '<?php return '.var_export($manifest, true).';'
        );
    }
}
