<?php
// Small display helpers for the Estates & Works module.

function estateProjectIcon(string $name): string
{
    $attrs = 'class="h-7 w-7 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"';
    $paths = [
        'crane' => '<path d="M3 21h18"></path><path d="M6 21V7h8"></path><path d="M6 7 3 4"></path><path d="M14 7l5 5"></path><path d="M19 12v3"></path><path d="M9 21v-8"></path>',
        'road' => '<path d="M6 20 10 4"></path><path d="m14 4 4 16"></path><path d="M12 8v2"></path><path d="M12 14v2"></path>',
        'barrier' => '<path d="M4 20v-6"></path><path d="M20 20v-6"></path><path d="M3 14h18"></path><path d="m5 14 5-6"></path><path d="m12 14 5-6"></path>',
        'bolt' => '<path d="M13 2 3 14h8l-1 8 10-12h-8z"></path>',
    ];

    return '<svg ' . $attrs . '>' . ($paths[$name] ?? $paths['crane']) . '</svg>';
}
