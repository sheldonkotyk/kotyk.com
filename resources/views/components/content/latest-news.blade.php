@props(['numberToShow' => 3, 'display' => 'vertical', 'cardsPerRow' => 3])
{{-- display and cardsPerRow belong to the old card grid; the latest posts are
     now a list, with the newest one shown with its picture. --}}
@php($posts = app(\App\Content\ContentRepository::class)->posts()->take($numberToShow))
<div class="not-prose mt-6">
    @forelse ($posts as $post)
        <x-blog.card :post="$post" :image="$loop->first" />
    @empty
        <p class="font-serif text-lg">Nothing's been posted yet.</p>
    @endforelse
    <div class="pt-6 border-t border-frost dark:border-zinc-800">
        <a href="/blog" class="link font-sans font-medium">Read every post</a>
    </div>
</div>
