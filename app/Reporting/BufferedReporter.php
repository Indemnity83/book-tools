<?php

namespace App\Reporting;

trait BufferedReporter
{
    protected array $buffer = [];

    public function line(string $message, array $context = []): void
    {
        $this->buffer[] = ['level' => 'line', 'message' => $message, 'context' => $context];
    }

    public function info(string $message, array $context = []): void
    {
        $this->buffer[] = ['level' => 'info', 'message' => $message, 'context' => $context];
    }

    public function warn(string $message, array $context = []): void
    {
        $this->buffer[] = ['level' => 'warn', 'message' => $message, 'context' => $context];
    }

    public function error(string $message, array $context = []): void
    {
        $this->buffer[] = ['level' => 'error', 'message' => $message, 'context' => $context];
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
        return collect($this->buffer)
            ->filter(fn ($e) => $e['level'] === $level)
            ->pluck('message')
            ->values()
            ->all();
    }
}
