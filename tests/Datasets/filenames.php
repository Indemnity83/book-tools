<?php

dataset('filenames', [
    'with series' => [
        [
            'authors' => ['Author'],
            'series' => ['Series #1'],
            'title' => 'Title',
        ],
        'Title, Book 1 of Series by Author',
    ],
    'without series' => [
        [
            'authors' => ['Author'],
            'series' => [],
            'title' => 'Title',
        ],
        'Title by Author',
    ],
]);
