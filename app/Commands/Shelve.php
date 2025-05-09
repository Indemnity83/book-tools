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

            $this->task('  - Shelving "'.$metadata->title.'" by '.$metadata->author, function () use ($bookFolder, $metadata, $destinationRoot) {
                $job = new BookImportJob(
                    $bookFolder,
                    $metadata,
                    $destinationRoot,
                    $this->makeFileOperator()
                );

                $report = $job->process();
                $this->reports[] = $report;

                return true;
            });
        });
    }

    protected function makeFileOperator()
    {
        return FileOperator::forConsole($this)
            ->withDryRun($this->isDryRun());
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
        $this->info('Shelving complete!');
        $this->info("Books processed: {$booksProcessed}");
        $this->info("Files moved: {$filesMoved}");
        $this->info("Folders deleted: {$foldersDeleted}");
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
