{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach ($entries as $entry)
    <url>
        <loc>{{ $entry->url() }}</loc>
@if ($entry->lastModified())
        <lastmod>{{ $entry->lastModified()->toDateString() }}</lastmod>
@endif
    </url>
@endforeach
    <url>
        <loc>{{ url('/tags') }}</loc>
@if ($tagsModified)
        <lastmod>{{ $tagsModified->toDateString() }}</lastmod>
@endif
    </url>
</urlset>
