<?php

namespace App\Contracts;

interface Reporter
{
    public function info(string $message): void;
    public function warn(string $message): void;
    public function error(string $message): void;
    public function line(string $message): void;
}
