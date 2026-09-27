<?php

/*
| Only the options this site changes; everything else falls through to
| Livewire's own config/livewire.php.
*/

return [

    'component_layout' => 'components.layouts.app',

    // The layout loads Livewire's script itself, and only on pages that carry
    // an interactive component: the script embeds a CSRF token, and a page
    // holding one must never be cached at the edge (see SetEdgeCacheHeaders).
    'inject_assets' => false,

];
