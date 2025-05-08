<?php

namespace App\Services;

class FileNameBuilder
{
    public static function build(BookMetadata $metadata, string $extension, ?int $partNumber = null): string
    {
        $base = $metadata->series
            ? "{$metadata->title}, Book {$metadata->seriesNumber} of {$metadata->series} by {$metadata->author}"
            : "{$metadata->title} by {$metadata->author}";

        if ($partNumber) {
            $base .= " (Part {$partNumber})";
        }

        return $base . '.' . $extension;
    }
}
