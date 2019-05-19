<?php

declare(strict_types=1);

namespace NunoMaduro\PhpInsights\Domain\Contracts;

/**
 * @internal
 */
interface Preset
{
    /**
     * Returns the preset name.
     */
    public static function getName(): string;

    /**
     * Returns the configuration preset.
     *
     * @return array<string, array|int|string>
     */
    public static function get(): array;

    /**
     * Determinates if the preset should be applied.
     *
     * @param array<string, array|int|string> $composer
     */
    public static function shouldBeApplied(array $composer): bool;
}
