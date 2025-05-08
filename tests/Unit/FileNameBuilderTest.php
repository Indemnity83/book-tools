<?php

use App\Services\BookMetadata;
use App\Services\FileNameBuilder;

it('builds filenames correctly', function (array $metadataArray, string $expected) {
    $metadata = new BookMetadata($metadataArray);

    $fileName = FileNameBuilder::build($metadata, 'm4b');

    expect($fileName)->toBe($expected . '.m4b');
})->with('filenames');

it('appends part number when provided', function () {
    $metadata = new BookMetadata([
        'authors' => ['Author'],
        'title' => 'Title',
    ]);

    expect(FileNameBuilder::build($metadata, 'm4b', null))->toBe('Title by Author.m4b');
    expect(FileNameBuilder::build($metadata, 'm4b', 1))->toBe('Title by Author (Part 1).m4b');
    expect(FileNameBuilder::build($metadata, 'm4b', 5))->toBe('Title by Author (Part 5).m4b');
});
