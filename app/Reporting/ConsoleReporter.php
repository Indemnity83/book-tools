<?php

namespace App\Reporting;

use App\Contracts\Reporter;
use Illuminate\Console\Command;

class ConsoleReporter implements Reporter
{
    protected Command $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function info(string $message): void
    {
        $this->command->info($message);
    }

    public function warn(string $message): void
    {
        $this->command->warn($message);
    }

    public function error(string $message): void
    {
        $this->command->error($message);
    }

    public function line(string $message): void
    {
        $this->command->line($message);
    }
}
