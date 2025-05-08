<?php

use Illuminate\Filesystem\Filesystem;

beforeEach(function () {
    // Use real Filesystem but with temporary path
    $this->fs = new Filesystem;
    $this->tmpDir = sys_get_temp_dir() . '/shelve_command_test_' . uniqid();
    $this->fs->ensureDirectoryExists($this->tmpDir);
});

afterEach(function () {
    $this->fs->deleteDirectory($this->tmpDir);
});

it('errors when import folder does not exist', function () {
    $this->artisan('shelve', [
        'importFolder' => $this->tmpDir . '/nonexistent'
    ])
        ->assertExitCode(1)
        ->expectsOutput('Import folder does not exist.');
});

it('processes book in dry run mode without moving files', function () {
    $import = $this->tmpDir . '/import';
    $this->fs->ensureDirectoryExists($import);

    // Create book folder with metadata and files
    $bookFolder = $import . '/book1';
    $this->fs->ensureDirectoryExists($bookFolder);

    file_put_contents("{$bookFolder}/metadata.json", json_encode([
        'authors' => ['Author'],
        'series' => ['Series #1'],
        'title' => 'Dry Run Book'
    ]));

    file_put_contents("{$bookFolder}/01.m4b", 'audio part');

    $destination = $this->tmpDir . '/library';

    $this->artisan('shelve', [
        'importFolder' => $import,
        'destinationFolder' => $destination,
        '--dry-run' => true
    ])
        ->assertExitCode(0)
        ->expectsOutputToContain('[Dry Run] Would move')
        ->expectsOutput('Shelving complete!')
        ->expectsOutput('Books processed: 1')
        ->expectsOutput('Files moved: 0')
        ->expectsOutput('Folders deleted: 0');

    // Source should still exist
    expect($this->fs->exists($bookFolder))->toBeTrue();
});

it('processes book and deletes folder when not dry run', function () {
    $import = $this->tmpDir . '/import';
    $this->fs->ensureDirectoryExists($import);

    $bookFolder = $import . '/book2';
    $this->fs->ensureDirectoryExists($bookFolder);

    file_put_contents("{$bookFolder}/metadata.json", json_encode([
        'authors' => ['Author'],
        'series' => ['Series #1'],
        'title' => 'Real Book'
    ]));

    file_put_contents("{$bookFolder}/01.m4b", 'audio part');

    $destination = $this->tmpDir . '/library';

    $this->artisan('shelve', [
        'importFolder' => $import,
        'destinationFolder' => $destination,
    ])
        ->assertExitCode(0)
        ->expectsOutput('Shelving complete!')
        ->expectsOutput('Books processed: 1')
        ->expectsOutput('Files moved: 2')
        ->expectsOutput('Folders deleted: 1');

    // Source should be deleted
    expect($this->fs->exists($bookFolder))->toBeFalse();

    // Destination files should exist
    expect($this->fs->exists($destination . '/Author/Series/1 - Real Book/Real Book, Book 1 of Series by Author.m4b'))->toBeTrue();
    expect($this->fs->exists($destination . '/Author/Series/1 - Real Book/metadata.json'))->toBeTrue();
});
