<?php

namespace App\Contracts;

interface Reporter
{
    public function simulate(string $action, array $context = []): void;

    public function info(string $message): void;

    public function warn(string $message): void;

    public function error(string $message): void;

    public function line(string $message): void;

    public function lines(): array;

    public function infos(): array;

    public function warnings(): array;

    public function errors(): array;

    public function flush(?int $verbosity = null): void;
}
