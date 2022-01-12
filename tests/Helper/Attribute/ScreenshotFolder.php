<?php

namespace App\Tests\Helper\Attribute;

use \Attribute;

#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
final class ScreenshotFolder
{
    public function __construct(
        public string $path
    ) {
    }
}
