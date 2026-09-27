@props(['head'])
<title>{{ $head->documentTitle() }}</title>
<meta name="description" content="{{ $head->metaDescription() }}">
<link rel="canonical" href="{{ $head->canonicalUrl() }}">
<link rel="home" href="{{ url('/') }}">
@if ($head->noindex)
    <meta name="robots" content="noindex, follow">
@else
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
@endif
<meta name="author" content="{{ config('seo.author.name') }}">
@if (config('seo.google_site_verification'))
    <meta name="google-site-verification" content="{{ config('seo.google_site_verification') }}">
@endif

<meta property="og:type" content="{{ $head->type }}">
<meta property="og:site_name" content="{{ config('seo.site_name') }}">
<meta property="og:locale" content="{{ config('seo.locale') }}">
<meta property="og:title" content="{{ $head->title ?? config('seo.site_name') }}">
<meta property="og:description" content="{{ $head->metaDescription() }}">
<meta property="og:url" content="{{ $head->canonicalUrl() }}">
@if ($head->imageUrl())
    <meta property="og:image" content="{{ $head->imageUrl() }}">
    @if ($head->image)
        <meta property="og:image:width" content="{{ config('images.presets.social.w') }}">
        <meta property="og:image:height" content="{{ config('images.presets.social.h') }}">
    @endif
    <meta property="og:image:alt" content="{{ $head->imageAlt ?? $head->title ?? config('seo.site_name') }}">
@endif
@if ($head->type === 'article')
    <meta property="article:author" content="{{ config('seo.author.name') }}">
    @if ($head->publishedAt)
        <meta property="article:published_time" content="{{ $head->publishedAt->toIso8601String() }}">
    @endif
    @if ($head->modifiedAt)
        <meta property="article:modified_time" content="{{ $head->modifiedAt->toIso8601String() }}">
    @endif
    @foreach ($head->tags as $tag)
        <meta property="article:tag" content="{{ $tag }}">
    @endforeach
@endif

<meta name="twitter:card" content="{{ $head->image ? 'summary_large_image' : 'summary' }}">
<meta name="twitter:site" content="{{ config('seo.twitter') }}">
<meta name="twitter:creator" content="{{ config('seo.twitter') }}">
<meta name="twitter:title" content="{{ $head->title ?? config('seo.site_name') }}">
<meta name="twitter:description" content="{{ $head->metaDescription() }}">
@if ($head->imageUrl())
    <meta name="twitter:image" content="{{ $head->imageUrl() }}">
@endif

<script type="application/ld+json">{!! json_encode($head->jsonLd(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG) !!}</script>
