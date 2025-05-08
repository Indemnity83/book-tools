<?php

namespace App\Services;

use Illuminate\Support\Str;

class BookMetadata
{
    public string $author;
    public ?string $series = null;
    public ?string $seriesNumber = null;
    public string $title;

    public function __construct(array $data)
    {
        $this->author = self::sanitize($data['authors'][0] ?? 'Unknown Author');

        if (!empty($data['series'][0])) {
            [$seriesName, $seriesNumber] = $this->parseSeries($data['series'][0]);
            $this->series = self::sanitize($seriesName);
            $this->seriesNumber = $seriesNumber;
        }

        $this->title = self::sanitize($data['title'] ?? 'Unknown Title');
    }

    public static function fromArray(array $data): self
    {
        return new self($data);
    }

    protected function parseSeries(string $series): array
    {
        if (preg_match('/^(.*)\s+#([\d\.]+)$/', $series, $matches)) {
            return [trim($matches[1]), $matches[2]];
        }

        return [$series, null];
    }

    protected static function sanitize(string $input): string
    {
        return Str::of($input)
            ->replace(['/', '\\', '?', '%', '*', ':', '|', '"', '<', '>'], ' ')
            ->replaceMatches('/\p{Extended_Pictographic}|\x{FE0F}/u', '')
            ->replaceMatches('/\s+/', ' ')
            ->trim()
            ->toString();
    }
}
