<?php

namespace App\Reporting;

use App\Contracts\Reporter;
use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Output\OutputInterface;

class ConsoleReporter implements Reporter
{
    use BufferedReporter;

    protected Command $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function flush(?int $verbosity = null, int $indent = 6, string $bulletStyle = '› '): void
    {
        if (is_null($verbosity)) {
            $verbosity = $this->command->getOutput()->getVerbosity();
        }

        $pad = $verbosity > OutputInterface::VERBOSITY_QUIET ? str_repeat(' ', $indent) : '';
        $bullet = $verbosity > OutputInterface::VERBOSITY_QUIET ? $bulletStyle : '';

        $levelThresholds = [
            'error' => OutputInterface::VERBOSITY_QUIET,
            'warn' => OutputInterface::VERBOSITY_VERBOSE,
            'info' => OutputInterface::VERBOSITY_VERY_VERBOSE,
            'line' => OutputInterface::VERBOSITY_VERY_VERBOSE,
        ];

        $this->renderDryRunSummary($verbosity, $pad);
        $this->renderBufferedMessages($verbosity, $pad, $bullet, $levelThresholds);

        $this->messageBuffer = [];
    }

    private function renderDryRunSummary(int $verbosity, string $pad): void
    {
        if (empty($this->dryRunActions)) {
            return;
        }

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
            $this->command->getOutput()->writeln($pad.'<fg=magenta>› Simulated actions:</>', OutputInterface::VERBOSITY_NORMAL);
            foreach ($summary as $line) {
                $this->command->getOutput()->writeln($pad.'  • <fg=magenta>'.$line.'</>', OutputInterface::VERBOSITY_NORMAL);
            }
        }

        $this->dryRunActions = [];
    }

    private function renderBufferedMessages(int $verbosity, string $pad, string $bullet, array $levelThresholds): void
    {
        foreach ($this->messageBuffer as $entry) {
            $level = $entry['level'];

            $styled = match ($level) {
                'info' => "<fg=cyan>{$bullet}{$entry['message']}</>",
                'warn' => "<fg=yellow>{$bullet}{$entry['message']}</>",
                'error' => "<fg=red>{$bullet}{$entry['message']}</>",
                default => "{$bullet}{$entry['message']}",
            };

            $this->command->getOutput()->writeln($pad.$styled, $levelThresholds[$level]);

            if (! empty($entry['context']) && $verbosity >= OutputInterface::VERBOSITY_VERY_VERBOSE) {
                foreach ($entry['context'] as $label => $value) {
                    $this->command->getOutput()->writeln($pad."  <fg=gray>{$label}:</> {$value}");
                }
            }
        }
    }
}
