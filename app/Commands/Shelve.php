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
    protected $signature = 'shelve {importFolder} {destinationFolder?} {--dry-run} {--pretend}
                                {--summary : Show summary report at the end}';

    protected $description = 'Organize and shelve audiobooks from the import folder into the destination folder (optional)';

    protected Filesystem $filesystem;

    protected array $reports = [];

    protected ConsoleReporter $reporter;

    public function __construct()
    {
        parent::__construct();

        $this->filesystem = new Filesystem;
        $this->reporter = new ConsoleReporter($this);

        app()->singleton(Reporter::class, fn () => $this->reporter);
    }

    public function handle()
    {
        if ($this->isDryRun()) {
            $this->warn('[Dry Run] No files will be moved or deleted.');
            $this->line('');
        }

        $importRoot = rtrim($this->argument('importFolder'), '/');
        $destinationRoot = rtrim($this->argument('destinationFolder') ?? getcwd(), '/');

        if (! $this->filesystem->exists($importRoot)) {
            $this->error('Import folder does not exist.');

            return self::FAILURE;
        }

        $bookFolders = $this->filesystem->directories($importRoot);

        if (empty($bookFolders)) {
            $this->info('No books found in import folder.');

            return self::SUCCESS;
        }

        foreach ($bookFolders as $bookFolder) {
            $this->processBook($bookFolder, $destinationRoot);
        }

        if ($this->option('summary')) {
            $this->outputSummary($this->reports);
        }

        return self::SUCCESS;
    }

    protected function processBook(string $bookFolder, string $destinationRoot): void
    {
        $metadataPath = $bookFolder.'/metadata.json';

        $this->taskGroup('Processing: '.basename($bookFolder), function () use ($metadataPath, $bookFolder, $destinationRoot) {
            $metadata = null;

            $this->task('  - Reading metadata', function () use (&$metadata, $metadataPath) {
                if (! $this->filesystem->exists($metadataPath)) {
                    $this->warn('Missing metadata file.');

                    return false;
                }

                try {
                    $metadata = BookMetadata::fromJsonFile($metadataPath);
                } catch (\Throwable $e) {
                    $this->warn('Failed to parse metadata: '.$e->getMessage());

                    return false;
                }

                return true;
            });

            if (! $metadata) {
                return;
            }

            $label = 'Shelving "'.$metadata->title.'" by '.$metadata->author;

            $this->task('  - '.$label, function () use ($bookFolder, $metadata, $destinationRoot) {
                $job = new BookImportJob(
                    $bookFolder,
                    $metadata,
                    $destinationRoot,
                    $this->makeFileOperator()
                );

                $report = $job->process();
                $this->reports[] = $report;

                return empty(app(Reporter::class)->errors());
            });

            app(Reporter::class)->flush();
        });
    }

    protected function makeFileOperator()
    {
        return app(FileOperator::class)->withDryRun($this->isDryRun());
    }

    protected function isDryRun(): bool
    {
        return $this->option('dry-run') || $this->option('pretend');
    }

    protected function outputSummary(array $reports): void
    {
        $booksProcessed = count($reports);
        $filesMoved = array_sum(array_map(fn ($r) => $r->filesMoved, $reports));
        $foldersDeleted = array_sum(array_map(fn ($r) => $r->folderDeleted ? 1 : 0, $reports));

        $this->info('-----------------------------------');
        $this->info($this->isDryRun() ? 'Dry run complete!' : 'Shelving complete!');
        $this->info("Books processed: {$booksProcessed}");
        $this->info('Files '.($this->isDryRun() ? 'that would have been moved' : 'moved').": {$filesMoved}");
        $this->info('Folders '.($this->isDryRun() ? 'that would have been deleted' : 'deleted').": {$foldersDeleted}");
    }

    protected function taskGroup(string $title, callable $callback): void
    {
        $this->info($title);
        $callback();
        $this->line('');
    }

    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
