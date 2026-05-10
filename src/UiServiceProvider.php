<?php

namespace Nawasara\Ui;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Nawasara\Ui\Services\WorkspaceManager;
use Symfony\Component\Finder\Finder;

class UiServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'nawasara-ui');

        $this->registerBlade();

        $this->registerLivewire();

        $this->registerPagination();

        $this->menuLoader();

        $this->offerPublishing();
    }

    /**
     * Override Laravel's AND Livewire's default pagination view with our
     * brand-themed one.
     *
     * Two layers because Livewire's WithPagination trait re-overrides the
     * Paginator default view per-request via its paginationView() method,
     * which defaults to 'livewire::tailwind'. Setting Paginator::defaultView
     * alone is insufficient for any Livewire component using WithPagination.
     *
     * Strategy:
     * 1. Paginator::defaultView for non-Livewire callers.
     * 2. Override the 'livewire::tailwind' view by re-registering the
     *    'livewire' namespace with our resources/views/vendor/livewire path
     *    at higher priority. The published file mirrors our pagination
     *    component so consumers don't have to know which view layer wins.
     */
    private function registerPagination(): void
    {
        Paginator::defaultView('nawasara-ui::components.pagination');
        // Prepend (not append) our path under the 'livewire' namespace so
        // Laravel's view finder picks our tailwind.blade.php BEFORE the one
        // bundled with the Livewire package. loadViewsFrom appends, which
        // loses every time because the bundled path is registered first.
        view()->prependNamespace('livewire', __DIR__.'/../resources/views/livewire-overrides');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/nawasara-ui.php', 'nawasara-ui');

        $this->app->singleton(WorkspaceManager::class, fn () => new WorkspaceManager());
        $this->app->alias(WorkspaceManager::class, 'nawasara.workspaces');
    }

    private function registerBlade(): void
    {
        // Guarded — Laravel's view:cache crashes on missing registered paths.
        if (is_dir(__DIR__.'/../resources/views/components')) {
            Blade::anonymousComponentPath(__DIR__.'/../resources/views/components', 'nawasara-ui');
        }
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
