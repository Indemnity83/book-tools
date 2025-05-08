<?php

use App\Services\BookMetadata;

it('parses and sanitizes metadata correctly', function ($data, $expectedTitle, $expectedAuthor, $expectedSeries, $expectedSeriesNumber) {
    $metadata = new BookMetadata($data);

    expect($metadata->author)->toBe($expectedAuthor)
        ->and($metadata->series)->toBe($expectedSeries)
        ->and($metadata->seriesNumber)->toBe($expectedSeriesNumber)
        ->and($metadata->title)->toBe($expectedTitle);
})->with('metadata');
