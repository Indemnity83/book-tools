<?php

use App\Services\BookMetadata;

it('parses and sanitizes metadata correctly', function ($data, $expectedTitle, $expectedAuthor, $expectedSeries, $expectedSeriesNumber) {
    $metadata = new BookMetadata($data);

    expect($metadata->author)->toBe($expectedAuthor)
        ->and($metadata->series)->toBe($expectedSeries)
        ->and($metadata->seriesNumber)->toBe($expectedSeriesNumber)
        ->and($metadata->title)->toBe($expectedTitle);
})->with('metadata');

it('parses metadata correctly from a valid file', function () {
    $path = tempnam(sys_get_temp_dir(), 'metadata_');
    file_put_contents($path, json_encode([
        'title' => 'Test Title',
        'authors' => ['Author One'],
        'series' => ['Series Name #3.5'],
    ]));

    $metadata = BookMetadata::fromJsonFile($path);

    expect($metadata->title)->toBe('Test Title');
    expect($metadata->author)->toBe('Author One');
    expect($metadata->series)->toBe('Series Name');
    expect($metadata->seriesNumber)->toBe('3.5');

    unlink($path);
});

it('throws if metadata file is missing', function () {
    BookMetadata::fromJsonFile('/path/to/nowhere.json');
})->throws(InvalidArgumentException::class, 'Metadata file not found');

it('throws if metadata file is invalid JSON', function () {
    $path = tempnam(sys_get_temp_dir(), 'badmeta_');
    file_put_contents($path, '{this-is-not-json');

    BookMetadata::fromJsonFile($path);
})->throws(RuntimeException::class, 'Invalid JSON');

it('handles null series as no series', function () {
    $metadata = new BookMetadata([
        'title' => 'Standalone Book',
        'authors' => ['Author'],
        'series' => [],
    ]);

    expect($metadata->series)->toBeNull();
    expect($metadata->seriesNumber)->toBeNull();
});

it('throws on malformed series format', function () {
    new BookMetadata([
        'title' => 'Broken Series',
        'authors' => ['Author'],
        'series' => ['Malformed Series'], // no number
    ]);
})->throws(RuntimeException::class, 'Invalid series format');
