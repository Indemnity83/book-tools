<?php

namespace App\Reporting;

use App\Contracts\Reporter;

class NullReporter implements Reporter
{
    public function info(string $message): void {}

    public function warn(string $message): void {}

    public function error(string $message): void {}

    public function line(string $message): void {}
}
