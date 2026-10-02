@props(['videos' => []])
<div class="not-prose grid grid-cols-1 gap-4 my-10 sm:grid-cols-2">
    @foreach ($videos as $video)
        <div class="relative overflow-hidden rounded-sm aspect-video">
            <iframe class="absolute inset-0 w-full h-full" src="{{ \App\Support\VideoEmbed::url($video) }}" title="Embedded video {{ $loop->iteration }}" loading="lazy" allowfullscreen></iframe>
        </div>
    @endforeach
</div>
