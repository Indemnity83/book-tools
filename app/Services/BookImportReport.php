<?php

namespace App\Services;

class BookImportReport
{
    public string $bookPath;
    public int $filesMoved = 0;
    public bool $folderDeleted = false;

    public function __construct(string $bookPath)
    {
        $this->bookPath = $bookPath;
    }
}
