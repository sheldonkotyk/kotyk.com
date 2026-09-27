<?php

namespace App\Providers;

use App\Content\ContentRepository;
use App\View\Head;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use League\Glide\Server;
use League\Glide\ServerFactory;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ContentRepository::class, fn () => new ContentRepository(config('content.path')));

        $this->app->scoped(Head::class);

        $this->app->singleton(Server::class, fn () => ServerFactory::create([
            'source' => Storage::disk(config('images.source_disk'))->getDriver(),
            'cache' => Storage::disk(config('images.cache_disk'))->getDriver(),
            'cache_path_prefix' => config('images.cache_prefix'),
            'driver' => config('images.driver'),
            'max_image_size' => config('images.max_image_size'),
            'presets' => config('images.presets'),
        ]));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::addNamespace('content', config('content.path'));
    }
}
