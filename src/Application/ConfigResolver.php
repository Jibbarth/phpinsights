<?php

declare(strict_types=1);

namespace NunoMaduro\PhpInsights\Application;

use NunoMaduro\PhpInsights\Application\Adapters\Laravel\Preset as LaravelPreset;
use NunoMaduro\PhpInsights\Application\Adapters\Symfony\Preset as SymfonyPreset;
use NunoMaduro\PhpInsights\Application\Adapters\Yii\Preset as YiiPreset;

/**
 * @internal
 */
final class ConfigResolver
{
    /**
     * @var string[]
     */
    private static $presets = [
        LaravelPreset::class,
        SymfonyPreset::class,
        YiiPreset::class,
        DefaultPreset::class,
    ];

    /**
     * Merge the given config with the specified preset.
     *
     * @param array<string, array|int|string> $config
     *
     * @return array<string, array>
     */
    public static function resolve(array $config, string $directory): array
    {
        $preset = $config['preset'] ?? self::guess($directory);

        foreach (self::$presets as $presetClass) {
            if ($presetClass::getName() === $preset) {
                $config = array_merge_recursive($presetClass::get(), $config);
            }
        }

        return $config;
    }

    /**
     * Guesses the preset based in information from the directory.
     */
    public static function guess(string $directory): string
    {
        $preset = 'default';

        $composerPath = $directory . \DIRECTORY_SEPARATOR . 'composer.json';

        if (! file_exists($composerPath)) {
            return $preset;
        }

        $composer = json_decode((string) file_get_contents($composerPath), true);

        foreach (self::$presets as $presetClass) {
            if ($presetClass::shouldBeApplied($composer)) {
                $preset = $presetClass::getName();

                break;
            }
        }

        return $preset;
    }
}
