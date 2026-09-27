@props(['videos' => []])
<div class="container grid grid-cols-1 mx-auto mt-4 md:grid-cols-3 md:gap-4">
    @foreach ($videos as $video)
        <div class="w-full rounded-lg shadow-sm">
            <div class="relative aspect-video">
                <iframe class="absolute inset-0 w-full h-full" src="{{ \App\Support\VideoEmbed::url($video) }}" title="Embedded video {{ $loop->iteration }}" loading="lazy" allowfullscreen></iframe>
            </div>
        </div>
    @endforeach
</div>
