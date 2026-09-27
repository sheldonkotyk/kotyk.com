<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Statamic\Entries\Entry as StatamicEntry;
use Statamic\Facades\Entry;
use Statamic\Facades\Nav;
use Statamic\Facades\Term;
use Statamic\Fieldtypes\Bard\Augmentor;
use Symfony\Component\Yaml\Yaml;

/**
 * One-time export of Statamic entries into Blade content files, run while
 * Statamic is still installed so its own Bard renderer produces the HTML.
 *
 * Each file is a Blade view whose front matter lives in a leading Blade
 * comment. Rich text becomes plain HTML; Bard sets become Blade component
 * (or Livewire) tags, so asset paths stay portable rather than being baked
 * into Glide URLs.
 */
class ExportStatamicContent extends Command
{
    protected $signature = 'cms:export-statamic {--path=resources/content : Where to write the content files}';

    protected $description = 'Export Statamic entries, tags and navigation into Blade content files';

    public function handle(): int
    {
        $root = base_path($this->option('path'));

        $nav = $this->navOrder();

        Entry::query()->whereIn('collection', ['pages', 'blogs'])->get()
            ->each(function (StatamicEntry $entry) use ($root, $nav) {
                $path = $this->pathFor($entry, $root);

                File::ensureDirectoryExists(dirname($path));
                File::put($path, $this->frontMatter($entry, $nav).$this->body($entry));

                $this->line('  '.Str::after($path, base_path().'/'));
            });

        File::put($root.'/tags.yaml', Yaml::dump(
            Term::query()->where('taxonomy', 'tags')->get()
                ->mapWithKeys(fn ($term) => [$term->slug() => $term->title()])
                ->sortKeys()
                ->all()
        ));

        $this->info('Exported content to '.$this->option('path'));

        return self::SUCCESS;
    }

    protected function pathFor(StatamicEntry $entry, string $root): string
    {
        if ($entry->collectionHandle() === 'blogs') {
            return "{$root}/blog/{$entry->slug()}.blade.php";
        }

        $uri = trim($entry->uri(), '/');

        return "{$root}/pages/".($uri === '' ? 'home' : $uri).'.blade.php';
    }

    /**
     * @return array<string, int> Entry ID => position in the main menu.
     */
    protected function navOrder(): array
    {
        return collect(Nav::find('main_menu')->in('default')->tree())
            ->pluck('entry')
            ->flip()
            ->map(fn ($position) => $position + 1)
            ->all();
    }

    protected function frontMatter(StatamicEntry $entry, array $nav): string
    {
        $data = array_filter([
            'title' => $entry->get('title'),
            'date' => $entry->collection()->dated() ? $entry->date()->format('Y-m-d H:i') : null,
            'updated' => $entry->get('updated_at') ? date('Y-m-d', $entry->get('updated_at')) : null,
            'published' => $entry->published() ? null : false,
            'template' => $entry->get('template'),
            'nav' => $nav[$entry->id()] ?? null,
            'tags' => $entry->get('tags') ?: null,
            'feature_image' => $this->firstAsset($entry->get('feature_image')),
            'teaser_image' => $this->firstAsset($entry->get('teaser_image')),
            'description' => $this->description($entry),
        ], fn ($value) => $value !== null);

        return "{{--\n".Yaml::dump($data, 2, 2, Yaml::DUMP_MULTI_LINE_LITERAL_BLOCK)."--}}\n";
    }

    protected function firstAsset(mixed $value): ?string
    {
        return is_array($value) ? ($value[0] ?? null) : $value;
    }

    protected function description(StatamicEntry $entry): ?string
    {
        $value = $entry->get('description');

        if (! $value) {
            return null;
        }

        $html = is_array($value) ? $this->renderRichText($entry, 'description', $value) : $value;

        return trim(html_entity_decode(strip_tags($html))) ?: null;
    }

    protected function body(StatamicEntry $entry): string
    {
        $nodes = $entry->get('content_area') ?? [];

        if (is_string($nodes)) {
            return $this->escapeBlade($nodes)."\n";
        }

        // Consecutive rich-text nodes render together as one HTML chunk; each
        // set in between becomes its own component tag.
        $chunks = [];
        $text = [];

        foreach ($nodes as $node) {
            if ($node['type'] !== 'set') {
                $text[] = $node;

                continue;
            }

            if ($text) {
                $chunks[] = $this->escapeBlade($this->renderRichText($entry, 'content_area', $text));
                $text = [];
            }

            if (($node['attrs']['enabled'] ?? true) !== false) {
                $chunks[] = $this->setTag($entry, $node['attrs']['values']);
            }
        }

        if ($text) {
            $chunks[] = $this->escapeBlade($this->renderRichText($entry, 'content_area', $text));
        }

        return implode("\n\n", $chunks)."\n";
    }

    protected function renderRichText(StatamicEntry $entry, string $field, array $nodes): string
    {
        $fieldtype = $entry->blueprint()->field($field)->fieldtype();

        $html = (new Augmentor($fieldtype))->renderProsemirrorToHtml(['type' => 'doc', 'content' => $nodes]);

        // One block element per line keeps the files diffable and hand-editable.
        return trim(preg_replace('#(</(p|h[1-6]|ul|ol|blockquote|table)>)(?=<)#', "$1\n", $html));
    }

    protected function setTag(StatamicEntry $entry, array $values): string
    {
        $type = $values['type'];

        if ($type === 'form') {
            return '<livewire:forms.'.str_replace('_', '-', $values['form']).' />';
        }

        // A quote's text is itself a Bard document, so it becomes the slot.
        if ($type === 'quote') {
            $quote = $this->escapeBlade($this->renderRichText($entry, 'content_area', $values['quote'] ?? []));
            $cite = filled($values['cite'] ?? null) ? ' cite="'.$this->escapeBlade($values['cite']).'"' : '';

            return "<x-content.quote{$cite}>\n{$quote}\n</x-content.quote>";
        }

        if ($type === 'two_videos') {
            $values['videos'] = collect($values['videos'] ?? [])
                ->reject(fn ($video) => ($video['enabled'] ?? true) === false)
                ->pluck('video_url')
                ->values()
                ->all();
        }

        $attributes = collect($values)
            ->except(['type', 'id', 'enabled'])
            ->reject(fn ($value) => $value === null || $value === '')
            ->map(function ($value, $key) {
                $name = str_replace('_', '-', $key);

                if (is_array($value)) {
                    return ":{$name}=\"".$this->phpLiteral($value).'"';
                }

                if (is_int($value) || is_bool($value)) {
                    return ":{$name}=\"".var_export($value, true).'"';
                }

                // Blade hands attribute strings to the component untouched, so
                // escaping here would be escaped twice. Pick a delimiter instead.
                $quote = str_contains($value, '"') ? "'" : '"';

                if (str_contains($value, '"') && str_contains($value, "'")) {
                    [$quote, $value] = ['"', str_replace('"', '&quot;', $value)];
                }

                return $name.'='.$quote.$this->escapeBlade($value).$quote;
            })
            ->implode(' ');

        return '<x-content.'.str_replace('_', '-', $type).($attributes ? ' '.$attributes : '').' />';
    }

    /**
     * Only flat lists reach here (see the two_videos case above).
     */
    protected function phpLiteral(array $value): string
    {
        return '['.collect($value)->map(fn ($item) => var_export($item, true))->implode(', ').']';
    }

    /**
     * Content is compiled as Blade, so escape anything Blade would treat as
     * syntax. A mid-word @ (emails, "@2x" filenames) is never a directive.
     */
    protected function escapeBlade(string $html): string
    {
        $html = str_replace(['{{', '{!!'], ['@{{', '@{!!'], $html);

        return preg_replace('/(?<![\\w@])@(?=\\w)/', '@@', $html);
    }
}
