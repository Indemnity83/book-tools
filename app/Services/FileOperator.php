<?php

namespace App\Services;

use App\Contracts\Reporter;
use App\Reporting\NullReporter;
use Illuminate\Filesystem\Filesystem;

class FileOperator
{
    protected bool $dryRun = false;

    protected array $simulatedDirectories = [];

    public function __construct(
        protected ?Reporter $reporter = null,
        protected ?Filesystem $files = null
    ) {
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
            $this->reporter->error('"'.basename($target).'" already exists', ['target' => $target]);

            return false;
        }

        $directory = dirname($target);

        if ($this->dryRun) {
            if (! $this->files->isDirectory($directory) && ! in_array($directory, $this->simulatedDirectories)) {
                $this->reporter->simulate('mkdir', ['directory' => $directory]);
                $this->simulatedDirectories[] = $directory;
            }

            $this->reporter->simulate($deleteSource ? 'move' : 'copy', ['source' => $source, 'target' => $target]);

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
