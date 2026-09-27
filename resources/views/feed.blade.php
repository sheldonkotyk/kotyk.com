{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<feed xmlns="http://www.w3.org/2005/Atom" xml:lang="{{ config('seo.language') }}" xml:base="{{ url('/') }}/">
    <id>{{ url('/') }}/</id>
    <title>{{ config('seo.site_name') }}</title>
    <subtitle>{{ config('seo.description') }}</subtitle>
    <link rel="alternate" type="text/html" href="{{ url('/blog') }}"/>
    <link rel="self" type="application/atom+xml" href="{{ route('feed') }}"/>
    <updated>{{ ($updated ?? now())->toAtomString() }}</updated>
    <author>
        <name>{{ config('seo.author.name') }}</name>
        <uri>{{ url('/') }}</uri>
    </author>
    <icon>{{ url(config('seo.image')) }}</icon>
@foreach ($posts as $post)
    <entry>
        <id>{{ $post->url() }}</id>
        <title>{{ $post->title }}</title>
        <link rel="alternate" type="text/html" href="{{ $post->url() }}"/>
        <published>{{ $post->date->toAtomString() }}</published>
        <updated>{{ $post->lastModified()->toAtomString() }}</updated>
@foreach ($post->tags as $tag)
        <category term="{{ $tag }}"/>
@endforeach
        <summary>{{ $post->summary() }}</summary>
        <content type="html">{{ view($post->view)->render() }}</content>
    </entry>
@endforeach
</feed>
