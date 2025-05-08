<?php

namespace App\Services;

use Illuminate\Filesystem\Filesystem;

class BookImportJob
{
    protected string $sourcePath;

    protected string $destinationPath;

    protected BookMetadata $metadata;

    protected FileOperator $fileOperator;

    protected Filesystem $filesystem;

    public function __construct(
        string $sourcePath,
        BookMetadata $metadata,
        string $destinationRoot,
        FileOperator $fileOperator,
        ?Filesystem $filesystem = null
    ) {
        $this->sourcePath = $sourcePath;
        $this->metadata = $metadata;
        $this->destinationPath = PathBuilder::build($metadata, $destinationRoot);
        $this->fileOperator = $fileOperator;
        $this->filesystem = $filesystem ?? new Filesystem;
    }

    public function process(): BookImportReport
    {
        $report = new BookImportReport($this->sourcePath);

        $files = collect($this->filesystem->files($this->sourcePath));

        // Process audio files
        $audioFiles = $files->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['m4b', 'mp3', 'flac']))
            ->sortBy->getFilename();

        $partNumber = 1;

        foreach ($audioFiles as $file) {
            $newFileName = FileNameBuilder::build($this->metadata, $file->getExtension(), $audioFiles->count() > 1 ? $partNumber : null);
            $targetPath = $this->destinationPath.'/'.$newFileName;

            if ($this->fileOperator->move($file->getPathname(), $targetPath)) {
                $report->filesMoved++;
            }

            $partNumber++;
        }

        // Process extra files
        $extraFiles = $files->filter(fn ($file) => in_array(strtolower($file->getExtension()), ['json', 'jpg', 'pdf']));

        foreach ($extraFiles as $file) {
            $targetPath = $this->destinationPath.'/'.$file->getFilename();

            if ($this->fileOperator->move($file->getPathname(), $targetPath)) {
                $report->filesMoved++;
            }
        }

        // Delete source folder if empty
        if (! $this->fileOperator->isDryRun()
            && count($this->filesystem->files($this->sourcePath)) === 0
            && count($this->filesystem->directories($this->sourcePath)) === 0) {

            $this->filesystem->deleteDirectory($this->sourcePath);
            $report->folderDeleted = true;
        }

        return $report;
    }
}
