<?php

namespace App\Providers;

use App\Content\ContentRepository;
use App\View\Head;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(ContentRepository::class, fn () => new ContentRepository(config('content.path')));

        $this->app->scoped(Head::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        View::addNamespace('content', config('content.path'));
    }
}
