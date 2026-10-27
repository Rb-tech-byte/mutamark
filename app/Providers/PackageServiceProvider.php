<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PackageServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $manifestPath = base_path('bootstrap/cache/workdo_packages.php');

        if (file_exists($manifestPath)) {
            $manifest = require $manifestPath;
        } else {
            $manifest = $this->buildManifest($manifestPath);
        }

        $loader = require base_path('vendor/autoload.php');

        foreach ($manifest['psr4'] ?? [] as $namespace => $path) {
            $loader->addPsr4($namespace, $path);
        }

        foreach ($manifest['providers'] ?? [] as $provider) {
            $this->app->register($provider);
        }
    }

    protected function buildManifest(string $manifestPath): array
    {
        $packageDirectories = glob(base_path('packages/workdo/*'), GLOB_ONLYDIR);
        $manifest = ['psr4' => [], 'providers' => []];

        foreach ($packageDirectories as $packageDir) {
            $composerFile = $packageDir . '/composer.json';

            if (file_exists($composerFile)) {
                $composerConfig = json_decode(file_get_contents($composerFile), true);

                if (isset($composerConfig['autoload']['psr-4'])) {
                    foreach ($composerConfig['autoload']['psr-4'] as $namespace => $path) {
                        $manifest['psr4'][$namespace] = $packageDir . '/' . $path;
                    }
                }

                if (isset($composerConfig['extra']['laravel']['providers'])) {
                    foreach ($composerConfig['extra']['laravel']['providers'] as $provider) {
                        $manifest['providers'][] = $provider;
                    }
                }
            }
        }

        @file_put_contents($manifestPath, '<?php return ' . var_export($manifest, true) . ';' . PHP_EOL);
        return $manifest;
    }

    public function boot(): void
    {
        //
    }
}