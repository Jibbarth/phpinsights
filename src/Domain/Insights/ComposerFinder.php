<?php

declare(strict_types=1);

namespace NunoMaduro\PhpInsights\Domain\Insights;

use NunoMaduro\PhpInsights\Domain\Collector;
use NunoMaduro\PhpInsights\Domain\Exceptions\ComposerNotFound;

final class ComposerFinder
{
    public static function contents(Collector $collector): string
    {
        $filePath = $collector->getDir() . '/composer.json';

        if (file_exists($filePath)) {
            return (string) file_get_contents($filePath);
        }

        throw new ComposerNotFound('`composer.json` not found.');
    }
}
