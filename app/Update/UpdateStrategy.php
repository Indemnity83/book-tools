<?php

namespace App\Update;

use Humbug\SelfUpdate\Strategy\GithubStrategy;
use LaravelZero\Framework\Components\Updater\Strategy\StrategyInterface;

class UpdateStrategy extends GithubStrategy implements StrategyInterface
{
    /**
     * Returns the Download Url.
     */
    protected function getDownloadUrl(array $package): string
    {
        parent::setPharName(config('app.name'));

        return parent::getDownloadUrl($package);
    }
}
