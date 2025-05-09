<?php

namespace App\Reporting;

use App\Contracts\Reporter;
use Illuminate\Console\Command;

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

        foreach ($this->buffer as $entry) {
            $key = $entry['message'];

            // Skip duplicate non-verbose messages
            if (! $verbose && $entry['level'] === 'error') {
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

            // Optionally print context info in verbose
            if ($verbose && ! empty($entry['context'])) {
                foreach ($entry['context'] as $label => $value) {
                    $this->command->line($pad."    <fg=gray>{$label}:</> {$value}");
                }
            }
        }

        $this->buffer = [];
    }
}
