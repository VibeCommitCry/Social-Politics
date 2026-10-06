<?php

declare(strict_types=1);

namespace App\Interface;

use Generator;

interface SyncFeedInterface
{
    public function search(string $url, callable $onCursor): Generator;

}
