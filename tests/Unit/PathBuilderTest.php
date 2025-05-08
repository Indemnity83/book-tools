<?php

use App\Services\BookMetadata;
use App\Services\PathBuilder;

it('builds path correctly', function (array $metadataArray, string $expected) {
    $metadata = new BookMetadata($metadataArray);

    $path = PathBuilder::build($metadata, '/books');

    expect($path)->toBe('/books' . $expected);
})->with('paths');
