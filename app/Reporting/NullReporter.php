<?php

namespace App\Reporting;

use App\Contracts\Reporter;

class NullReporter implements Reporter
{
    use BufferedReporter;

    public function flush(?int $verbose = null): void
    {
        $this->messageBuffer = []; // no-op
    }
}
