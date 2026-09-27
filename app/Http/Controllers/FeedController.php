<?php

namespace App\Http\Controllers;

use App\Content\ContentRepository;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    /**
     * Atom feed of the latest posts, with their full content.
     */
    public function __invoke(ContentRepository $content): Response
    {
        $posts = $content->posts()->take(20);

        return response()
            ->view('feed', ['posts' => $posts, 'updated' => $posts->first()?->lastModified()])
            ->header('Content-Type', 'application/atom+xml; charset=utf-8');
    }
}
