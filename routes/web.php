<?php

use App\Http\Controllers\FeedController;
use App\Http\Controllers\ImageController;
use App\Http\Controllers\RedirectMailSubdomain;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\WeatherController;
use App\Http\Middleware\RedirectNonCanonicalHost;
use Illuminate\Support\Facades\Route;

// Redirect the mail subdomain to webmail. Registered here so it is matched
// before the content catch-all at the bottom of this file.
// A 302 rather than a 301: browsers cache 301s indefinitely, and the target
// should stay changeable without visitors being stuck on the old one.
Route::domain(config('redirects.mail_subdomain.host'))->group(function () {
    Route::get('/{path?}', RedirectMailSubdomain::class)
        ->where('path', '.*')
        ->name('mail-subdomain.redirect');
});

// Resized content images. Outside the web group: without a session there is
// no Set-Cookie, so the edge is willing to cache them.
Route::get('/img/{preset}/{path}', ImageController::class)
    ->where('path', '.*')
    ->withoutMiddleware('web')
    ->name('image');

// The weather over Steinbach, for the sky. Outside the web group for the same
// reason as images: no session cookie, so the edge caches it.
Route::get('/weather.json', WeatherController::class)
    ->withoutMiddleware('web')
    ->name('weather');

// The sitemap and the feed, outside the web group for the same reason: no
// session cookie, so the edge caches them and a crawler checking them doesn't
// wake the container. They keep the canonical-host redirect.
Route::get('/sitemap.xml', SitemapController::class)
    ->withoutMiddleware('web')
    ->middleware(RedirectNonCanonicalHost::class)
    ->name('sitemap');
Route::get('/feed', FeedController::class)
    ->withoutMiddleware('web')
    ->middleware(RedirectNonCanonicalHost::class)
    ->name('feed');

Route::livewire('/blog', 'pages::blog.index')->name('blog.index');
Route::livewire('/blog/{slug}', 'pages::blog.show')->name('blog.show');
Route::livewire('/tags', 'pages::tags.index')->name('tags.index');
Route::livewire('/tags/{tag}', 'pages::tags.show')->name('tags.show');

// Every other path is a page under resources/content/pages. Must stay last.
Route::livewire('/{uri?}', 'pages::page')->where('uri', '.*')->name('page');
