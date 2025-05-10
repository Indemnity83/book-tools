<?php

namespace App\Reporting;

trait BufferedReporter
{
    protected array $messageBuffer = [];

    protected array $dryRunActions = [];

    public function simulate(string $type, array $context = []): void
    {
        $this->dryRunActions[] = compact('type', 'context');
    }

    public function line(string $message, array $context = []): void
    {
        $this->messageBuffer[] = ['level' => 'line', 'message' => $message, 'context' => $context];
    }

    public function info(string $message, array $context = []): void
    {
        $this->messageBuffer[] = ['level' => 'info', 'message' => $message, 'context' => $context];
    }

    public function warn(string $message, array $context = []): void
    {
        $this->messageBuffer[] = ['level' => 'warn', 'message' => $message, 'context' => $context];
    }

    public function error(string $message, array $context = []): void
    {
        $this->messageBuffer[] = ['level' => 'error', 'message' => $message, 'context' => $context];
    }

    public function dryRuns(): array
    {
        return $this->filtered('dry_run');
    }

    public function lines(): array
    {
        return $this->filtered('line');
    }

    public function infos(): array
    {
        return $this->filtered('info');
    }

    public function warnings(): array
    {
        return $this->filtered('warn');
    }

    public function errors(): array
    {
        return $this->filtered('error');
    }

    protected function filtered(string $level): array
    {
        return collect($this->messageBuffer)
            ->filter(fn ($e) => $e['level'] === $level)
            ->pluck('message')
            ->values()
            ->all();
    }
}
