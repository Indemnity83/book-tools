<?php

namespace App\Reporting;

use App\Contracts\Reporter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class ConsoleReporter implements Reporter
{
    use BufferedReporter;

    protected Command $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function flush(bool $verbose = false, int $indent = 6, string $bullet = '›'): void
    {
        $pad = str_repeat(' ', $indent);
        $seen = [];

        // Summarize dry run actions
        if (! empty($this->dryRunActions)) {
            $moves = array_filter($this->dryRunActions, fn ($a) => $a['type'] === 'move');
            $copies = array_filter($this->dryRunActions, fn ($a) => $a['type'] === 'copy');
            $dirs = array_filter($this->dryRunActions, fn ($a) => $a['type'] === 'mkdir');

            $summary = [];

            if (count($dirs) > 0) {
                $summary[] = count($dirs).' '.Str::plural('directory', count($dirs)).' would be created';
            }

            if (count($moves) > 0) {
                $summary[] = count($moves).' '.Str::plural('file', count($moves)).' would be moved';
            }

            if (count($copies) > 0) {
                $summary[] = count($copies).' '.Str::plural('file', count($copies)).' would be copied';
            }

            if (! empty($summary)) {
                $this->command->line($pad.'<fg=magenta>› Simulated actions:</>');
                foreach ($summary as $line) {
                    $this->command->line($pad.'  • <fg=magenta>'.$line.'</>');
                }
            }

            $this->dryRunActions = [];
        }

        // Print other buffered messages
        foreach ($this->messageBuffer as $entry) {
            $key = $entry['message'];

            if (! $verbose) {
                if ($entry['level'] !== 'error') {
                    continue;
                }

                // Deduplicate non-verbose errors
                if (isset($seen[$key])) {
                    continue;
                }
                $seen[$key] = true;
            }

            $styled = match ($entry['level']) {
                'info' => "<fg=cyan>{$bullet} {$entry['message']}</>",
                'warn' => "<fg=yellow>{$bullet} {$entry['message']}</>",
                'error' => "<fg=red>{$bullet} {$entry['message']}</>",
                default => "{$bullet} {$entry['message']}",
            };

            $this->command->line($pad.$styled);

            if ($verbose && ! empty($entry['context'])) {
                foreach ($entry['context'] as $label => $value) {
                    $this->command->line($pad."  <fg=gray>{$label}:</> {$value}");
                }
            }
        }

        $this->messageBuffer = [];
    }
}
