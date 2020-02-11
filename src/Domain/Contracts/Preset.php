<?php

declare(strict_types=1);

namespace NunoMaduro\PhpInsights\Domain\Contracts;

use NunoMaduro\PhpInsights\Domain\Composer\Composer;

/**
 * @internal
 */
interface Preset
{
    /**
     * Returns the preset name.
     *
     * @return string
     */
    public static function getName(): string;

    /**
     * Returns the configuration preset.
     *
     * @param \NunoMaduro\PhpInsights\Domain\Composer\Composer $composer
     *
     * @return array<string, string|int|array>
     */
    public static function get(Composer $composer): array;

    /**
     * Determinates if the preset should be applied.
     *
     * @param \NunoMaduro\PhpInsights\Domain\Composer\Composer $composer
     *
     * @return bool
     */
    public static function shouldBeApplied(Composer $composer): bool;
}
