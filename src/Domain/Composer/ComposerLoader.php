<?php

declare(strict_types=1);

namespace NunoMaduro\PhpInsights\Domain\Composer;

use Composer\Composer;
use Composer\Factory;
use Composer\IO\NullIO;
use NunoMaduro\PhpInsights\Domain\Collector;

/**
 * @internal
 */
final class ComposerLoader
{
    public static function getInstance(Collector $collector): Composer
    {
        $io = new NullIO();
        return Factory::create($io, ComposerFinder::getPath($collector));
    }
}
