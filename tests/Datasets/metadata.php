<?php

dataset('metadata', [
    'with series' => [
        [
            'title' => 'An Absolutely Remarkable Thing',
            'authors' => ['Hank Green'],
            'series' => ['The Carls #1'],
        ],
        'An Absolutely Remarkable Thing',
        'Hank Green',
        'The Carls',
        '1',
    ],
    'without series' => [
        [
            'title' => 'Tubes',
            'authors' => ['Andrew Blum'],
            'series' => [],
        ],
        'Tubes',
        'Andrew Blum',
        null,
        null,
    ],
    'without multiple authors' => [
        [
            'title' => 'Bastille vs. the Evil Librarians',
            'authors' => ['Brandon Sanderson', 'Janci Patterson'],
            'series' => ['Alcatraz vs. the Evil Librarians #6'],
        ],
        'Bastille vs. the Evil Librarians',
        'Brandon Sanderson',
        'Alcatraz vs. the Evil Librarians',
        '6',
    ],
    'without a decimal series number' => [
        [
            'title' => 'Sunreach',
            'authors' => ['Brandon Sanderson', 'Janci Patterson'],
            'series' => ['The Skyward Series #2.1'],
        ],
        'Sunreach',
        'Brandon Sanderson',
        'The Skyward Series',
        '2.1',
    ],
    'with forbidden characters' => [
        [
            'title' => 'Arcanum Unbounded: The Cosmere Collection',
            'authors' => ['Brandon Sanderson'],
            'series' => [],
        ],
        'Arcanum Unbounded The Cosmere Collection',
        'Brandon Sanderson',
        null,
        null,
    ],
    'with special characters' => [
        [
            'title' => 'The Fright of Real Tears: Krzysztof Kieślowski Between Theory and Post-Theory',
            'authors' => ['Slavoj Žižek'],
            'series' => [],
        ],
        'The Fright of Real Tears Krzysztof Kieślowski Between Theory and Post-Theory',
        'Slavoj Žižek',
        null,
        null,
    ],
    'with emojis' => [
        [
            'title' => 'The Future of AI 🤖',
            'authors' => ['TechTalk Podcast 🎧'],
            'series' => ['Tech Talk 🎙️ #57'],
        ],
        'The Future of AI',
        'TechTalk Podcast',
        'Tech Talk',
        '57',
    ],
]);
