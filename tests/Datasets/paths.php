<?php

dataset('paths', [
    'with series' => [
        [
            'authors' => ['Author'],
            'series' => ['Series #1'],
            'title' => 'Title',
        ],
        '/Author/Series/1 - Title',
    ],
    'without series' => [
        [
            'authors' => ['Author'],
            'title' => 'Title',
        ],
        '/Author/Title',
    ],
]);
