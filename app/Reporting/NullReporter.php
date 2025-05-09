<?php

namespace App\Reporting;

use App\Contracts\Reporter;

class NullReporter implements Reporter
{
    use BufferedReporter;

    public function flush(bool $verbose = false): void
    {
        $this->messageBuffer = []; // no-op
    }
}
