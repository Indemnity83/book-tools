<?php

namespace App\Commands;

use App\Contracts\Reporter;
use App\Reporting\ConsoleReporter;
use App\Services\BookImportJob;
use App\Services\BookMetadata;
use App\Services\FileOperator;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Filesystem\Filesystem;
use LaravelZero\Framework\Commands\Command;

class Shelve extends Command
{
    protected $signature = 'shelve {importFolder} {destinationFolder?} {--dry-run} {--pretend}';

    protected $description = 'Organize and shelve audiobooks from the import folder into the destination folder (optional)';

    protected Filesystem $filesystem;

    public function __construct()
    {
        parent::__construct();

        $this->filesystem = new Filesystem;
    }

    public function handle()
    {
        app()->bind(Reporter::class, fn () => new ConsoleReporter($this));

        $importRoot = rtrim($this->argument('importFolder'), '/');
        $destinationRoot = rtrim($this->argument('destinationFolder') ?? getcwd(), '/');

        if (! $this->filesystem->exists($importRoot)) {
            $this->error('Import folder does not exist.');

            return self::FAILURE;
        }

        $bookFolders = $this->filesystem->directories($importRoot);
        $reports = [];

        // Prepare FileOperator with correct dry run setting
        $fileOperator = FileOperator::forConsole($this)->withDryRun($this->isDryRun());

        foreach ($bookFolders as $bookFolder) {
            $metadataPath = $bookFolder.'/metadata.json';

            if (! $this->filesystem->exists($metadataPath)) {
                $this->warn("Skipping {$bookFolder}, no metadata found.");

                continue;
            }

            $metadata = BookMetadata::fromArray(json_decode(file_get_contents($metadataPath), true));

            $job = new BookImportJob(
                $bookFolder,
                $metadata,
                $destinationRoot,
                $fileOperator,
                $this->filesystem
            );

            $reports[] = $job->process();
        }

        $this->outputSummary($reports);

        return self::SUCCESS;
    }

    protected function isDryRun(): bool
    {
        return $this->option('dry-run') || $this->option('pretend');
    }

    protected function outputSummary(array $reports): void
    {
        $this->info('-----------------------------------');
        $this->info('Shelving complete!');
        $this->info('Books processed: '.count($reports));
        $this->info('Files moved: '.array_sum(array_map(fn ($r) => $r->filesMoved, $reports)));
        $this->info('Folders deleted: '.array_sum(array_map(fn ($r) => $r->folderDeleted ? 1 : 0, $reports)));
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
