<?php

namespace App\Http\Controllers;

use App\Content\ContentRepository;
use App\Content\Entry;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    /**
     * Every listed page and post. Tag archives are noindex, so they are left
     * out; changefreq and priority are omitted because search engines ignore them.
     */
    public function __invoke(ContentRepository $content): Response
    {
        $entries = $content->published()
            ->filter(fn (Entry $entry) => $entry->isListed())
            ->sortBy(fn (Entry $entry) => [$entry->isPost(), $entry->uri])
            ->values();

        $tagsModified = $content->posts()->first()?->lastModified();

        return response()
            ->view('sitemap', ['entries' => $entries, 'tagsModified' => $tagsModified])
            ->header('Content-Type', 'application/xml; charset=utf-8');
    }
}
