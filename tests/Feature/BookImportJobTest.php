<?php

use App\Reporting\NullReporter;
use App\Services\BookImportJob;
use App\Services\BookMetadata;
use App\Services\FileOperator;
use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    $this->fs = new Filesystem;
    $this->tmpDir = sys_get_temp_dir().'/book_import_test_'.uniqid();
    $this->fs->ensureDirectoryExists($this->tmpDir);
});

afterEach(function () {
    $this->fs->deleteDirectory($this->tmpDir);
});

it('moves audio and extra files and deletes folder when not dry run', function () {
    // Setup source folder with files
    $source = $this->tmpDir.'/book';
    $this->fs->ensureDirectoryExists($source);

    // Create audio files
    file_put_contents("{$source}/01.m4b", 'audio part 1');
    file_put_contents("{$source}/02.m4b", 'audio part 2');

    // Create extra files
    file_put_contents("{$source}/metadata.json", json_encode([
        'authors' => ['Author'],
        'series' => ['Series #1'],
        'title' => 'Test Book',
    ]));

    file_put_contents("{$source}/cover.jpg", 'cover image');

    // Destination root
    $destination = $this->tmpDir.'/library';

    // Metadata
    $metadata = new BookMetadata([
        'authors' => ['Author'],
        'series' => ['Series #1'],
        'title' => 'Test Book',
    ]);

    // File operator (real mode, not dry run)
    $operator = new FileOperator(new NullReporter);

    $job = new BookImportJob($source, $metadata, $destination, $operator, $this->fs);

    $report = $job->process();

    // Assert
    expect($report->filesMoved)->toBe(4); // 2 audio + metadata.json + cover.jpg
    expect($report->folderDeleted)->toBeTrue();

    // Destination folder should have files
    expect($this->fs->exists($destination.'/Author/Series/1 - Test Book/Test Book, Book 1 of Series by Author (Part 1).m4b'))->toBeTrue();
    expect($this->fs->exists($destination.'/Author/Series/1 - Test Book/Test Book, Book 1 of Series by Author (Part 2).m4b'))->toBeTrue();
    expect($this->fs->exists($destination.'/Author/Series/1 - Test Book/metadata.json'))->toBeTrue();
    expect($this->fs->exists($destination.'/Author/Series/1 - Test Book/cover.jpg'))->toBeTrue();

    // Source folder should be deleted
    expect($this->fs->exists($source))->toBeFalse();
});

it('does not delete folder in dry run', function () {
    // Setup source folder
    $source = $this->tmpDir.'/book2';
    $this->fs->ensureDirectoryExists($source);
    file_put_contents("{$source}/01.m4b", 'audio part');

    // Destination root
    $destination = $this->tmpDir.'/library';

    // Metadata
    $metadata = new BookMetadata([
        'authors' => ['Author'],
        'title' => 'Another Book',
    ]);

    $operator = (new FileOperator(new NullReporter))->withDryRun(true);

    $job = new BookImportJob($source, $metadata, $destination, $operator, $this->fs);

    $report = $job->process();

    expect($report->filesMoved)->toBe(0);
    expect($report->folderDeleted)->toBeFalse();
    expect($this->fs->exists($source))->toBeTrue();
});
