# Kotyk.com

The online abode of Sheldon Kotyk. A Laravel app whose pages are Blade files
rendered by Livewire; there is no database-backed CMS and no admin panel.

## Content

Everything lives in `resources/content`, edited in any editor and deployed with git.

- `pages/{path}.blade.php` is served at `/{path}`; `pages/home.blade.php` is `/`.
- `blog/{slug}.blade.php` is served at `/blog/{slug}`.
- `tags.yaml` maps tag slugs to their display names.

Each file opens with YAML front matter inside a Blade comment:

```blade
{{--
title: 'Vision doesn’t just leak, it is squeezed'
date: '2025-09-12 14:32'       # posts only; future dates stay unlisted until then
updated: '2025-09-18'          # sitemap lastmod and article:modified_time
tags: [vision, leadership]
feature_image: pages/bucket.png  # path on the assets disk, also the social card image
description: Optional meta description; defaults to the opening text.
nav: 2                         # pages only: position in the main menu
published: false               # hides the entry entirely
--}}
<p>Body HTML, styled with Tailwind.</p>

<x-content.image-with-caption image="pages/sponges.png" caption="Sponges" locate="left" />
```

Content blocks live in `resources/views/components/content`: `image-with-caption`,
`video`, `two-videos`, `quote`, `button`, `divider` and `latest-news`. Forms are
Livewire components: `<livewire:forms.contact />` and
`<livewire:forms.ds-dispatch-notifications />`. A page containing one loads
Livewire's script and is never cached at the edge; every other page is.

Images and other media live on the `s3` disk (Cloudflare R2).

## SEO

`config/seo.php` holds the site name, default description, author and social
profiles. Every page gets a canonical URL, meta description, Open Graph and
Twitter tags, and JSON-LD; the site also serves `/sitemap.xml` and an Atom feed
at `/feed`.

## Development

```sh
composer install && npm install
npm run build
php artisan test
```

Deploy with `bin/deploy.sh`, which builds, tests, deploys to Laravel Cloud and
then warms the edge from the sitemap.
