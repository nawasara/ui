<?php

namespace Nawasara\Ui;

use Livewire\Livewire;
use Illuminate\Support\Str;
use Symfony\Component\Finder\Finder;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Nawasara\Ui\Services\WorkspaceManager;

class UiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nawasara-ui');

        $this->registerBlade();

        $this->registerLivewire();

        $this->menuLoader();

        $this->offerPublishing();
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nawasara-ui.php', 'nawasara-ui');

        $this->app->singleton(WorkspaceManager::class, fn () => new WorkspaceManager());
        $this->app->alias(WorkspaceManager::class, 'nawasara.workspaces');
    }

    private function registerBlade(): void
    {
        Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'nawasara-ui');
    }

    public function registerLivewire(): void
    {
        $namespace = 'Nawasara\\Ui\\Livewire';
        $basePath = __DIR__.'/Livewire';

        if (! is_dir($basePath)) {
            return;
        }

        $finder = new Finder();
        $finder->files()->in($basePath)->name('*.php');

        foreach ($finder as $file) {
            $relativePath = str_replace('/', '\\', $file->getRelativePathname());
            $class = $namespace.'\\'.Str::beforeLast($relativePath, '.php');

            if (class_exists($class)) {
                $alias = 'nawasara-ui.'.
                    Str::of($relativePath)
                        ->replace('.php', '')
                        ->replace('\\', '.')
                        ->replace('/', '.')
                        ->explode('.')
                        ->map(fn ($segment) => Str::kebab($segment))
                        ->join('.');

                Livewire::component($alias, $class);
            }
        }
    }

    public function menuLoader(): void
    {
        $menus = [];

        foreach (glob(base_path('vendor/nawasara/*/config/menu.php')) as $menuPath) {
            $menuConfig = require $menuPath;
            if (is_array($menuConfig)) {
                $menus = array_merge($menus, $menuConfig);
            }
        }

        app()->instance('nawasara.menu', $menus);
    }

    protected function offerPublishing(): void
    {
        if (! $this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__.'/../config/nawasara-ui.php' => config_path('nawasara-ui.php'),
        ], 'nawasara-ui:config');

        $this->publishes([
            __DIR__.'/../public' => public_path('vendor/nawasara-ui'),
        ], 'nawasara-ui:public');
    }
}
