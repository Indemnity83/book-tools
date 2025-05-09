<?php

namespace App\Services;

use App\Contracts\Reporter;
use App\Reporting\NullReporter;
use Illuminate\Filesystem\Filesystem;

class FileOperator
{
    protected bool $dryRun = false;

    protected Filesystem $files;

    protected Reporter $reporter;

    protected array $simulatedDirectories = [];

    public function __construct(?Reporter $reporter = null, ?Filesystem $files = null)
    {
        $this->reporter = $reporter ?? new NullReporter;
        $this->files = $files ?? new Filesystem;
    }

    public function withDryRun(bool $dryRun): self
    {
        $this->dryRun = $dryRun;

        return $this;
    }

    public function copy(string $source, string $target): bool
    {
        return $this->perform($source, $target, false);
    }

    public function move(string $source, string $target): bool
    {
        return $this->perform($source, $target, true);
    }

    protected function perform(string $source, string $target, bool $deleteSource): bool
    {
        if ($this->files->exists($target)) {
            $message = $this->dryRun
                ? 'Would refuse to overwrite an existing file'
                : 'Refusing to overwrite an existing file';

            $this->reporter->error($message, ['target' => $target]);

            return false;
        }

        $directory = dirname($target);

        if ($this->dryRun) {
            if (! $this->files->isDirectory($directory) && ! in_array($directory, $this->simulatedDirectories)) {
                $this->reporter->line('[Dry Run] Would make directory', ['directory' => $directory]);
                $this->simulatedDirectories[] = $directory;
            }

            $this->reporter->line('[Dry Run] Would '.($deleteSource ? 'move' : 'copy').' a file', ['source' => $source, 'target' => $target]);

            return false;
        }

        $this->files->ensureDirectoryExists($directory);

        if ($deleteSource) {
            $this->files->move($source, $target);
        } else {
            $this->files->copy($source, $target);
        }

        $this->reporter->info(ucfirst($deleteSource ? 'Moved' : 'Copied').": {$source} -> {$target}");

        return true;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }
}
