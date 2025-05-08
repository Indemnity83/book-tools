<?php

namespace App\Services;

class PathBuilder
{
    public static function build(BookMetadata $metadata, string $destinationRoot): string
    {
        if ($metadata->series) {
            return "{$destinationRoot}/{$metadata->author}/{$metadata->series}/{$metadata->seriesNumber} - {$metadata->title}";
        }

        return "{$destinationRoot}/{$metadata->author}/{$metadata->title}";
    }
}
