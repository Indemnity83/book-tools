<?php

namespace App\Services;

use App\Contracts\Reporter;
use App\Reporting\ConsoleReporter;
use App\Reporting\NullReporter;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class FileOperator
{
    protected bool $dryRun = false;

    protected Filesystem $files;

    protected Reporter $reporter;

    public function __construct(?Reporter $reporter = null, ?Filesystem $files = null)
    {
        $this->reporter = $reporter ?? new NullReporter;
        $this->files = $files ?? new Filesystem;
    }

    public static function forConsole(Command $command): self
    {
        return new self(new ConsoleReporter($command));
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
        if ($this->dryRun) {
            $this->reporter->line('[Dry Run] Would '.($deleteSource ? 'move' : 'copy')." {$source} -> {$target}");

            return false;
        }

        $this->files->ensureDirectoryExists(dirname($target));

        if ($deleteSource) {
            $this->files->move($source, $target);
        } else {
            $this->files->copy($source, $target);
        }

        $this->reporter->info(($deleteSource ? 'Moved' : 'Copied').": {$source} -> {$target}");

        return true;
    }

    public function isDryRun(): bool
    {
        return $this->dryRun;
    }

    public function getReporter(): Reporter
    {
        return $this->reporter;
    }
}
