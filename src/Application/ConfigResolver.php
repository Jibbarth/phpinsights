<?php

declare(strict_types=1);

namespace NunoMaduro\PhpInsights\Application;

use NunoMaduro\PhpInsights\Application\Adapters\Drupal\Preset as DrupalPreset;
use NunoMaduro\PhpInsights\Application\Adapters\Laravel\Preset as LaravelPreset;
use NunoMaduro\PhpInsights\Application\Adapters\Magento2\Preset as Magento2Preset;
use NunoMaduro\PhpInsights\Application\Adapters\Symfony\Preset as SymfonyPreset;
use NunoMaduro\PhpInsights\Application\Adapters\Yii\Preset as YiiPreset;
use NunoMaduro\PhpInsights\Domain\Contracts\FileLinkFormatter as FileLinkFormatterContract;
use NunoMaduro\PhpInsights\Domain\Contracts\Preset;
use NunoMaduro\PhpInsights\Domain\Exceptions\PresetNotFound;
use NunoMaduro\PhpInsights\Domain\LinkFormatter\FileLinkFormatter;
use NunoMaduro\PhpInsights\Domain\LinkFormatter\NullFileLinkFormatter;

/**
 * @internal
 */
final class ConfigResolver
{
    /**
     * @var array<string>
     */
    private static $presets = [
        DrupalPreset::class,
        LaravelPreset::class,
        SymfonyPreset::class,
        YiiPreset::class,
        Magento2Preset::class,
        DefaultPreset::class,
    ];

    /**
     * Merge the given config with the specified preset.
     *
     * @param  array<string, string|int|array>  $config
     * @param  string  $directory
     *
     * @return array<string, array>
     */
    public static function resolve(array $config, string $directory): array
    {
        $config['fileLinkFormatter'] = self::resolveIde($config);
        unset($config['ide']);

        /** @var string $preset */
        $preset = $config['preset'] ?? self::guess($directory);

        /** @var Preset $presetClass */
        foreach (self::$presets as $presetClass) {
            if ($presetClass::getName() === $preset) {
                return self::mergeConfig($presetClass::get(), $config);
            }
        }

        throw new PresetNotFound(sprintf('%s not found', $preset));
    }

    /**
     * Guesses the preset based in information from the directory.
     *
     * @param  string  $directory
     *
     * @return string
     */
    public static function guess(string $directory): string
    {
        $preset = 'default';

        $composerPath = $directory . DIRECTORY_SEPARATOR . 'composer.json';

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

    /**
     * @see https://www.php.net/manual/en/function.array-merge-recursive.php#96201
     *
     * @param mixed[] $base
     * @param mixed[] $replacement
     *
     * @return array<string, array>
     */
    public static function mergeConfig(array $base, array $replacement): array
    {
        foreach ($replacement as $key => $value) {
            if (! array_key_exists($key, $base) && ! is_numeric($key)) {
                $base[$key] = $replacement[$key];
                continue;
            }
            if (is_array($value) || (array_key_exists($key, $base) && is_array($base[$key]))) {
                $base[$key] = self::mergeConfig($base[$key], $replacement[$key]);
            } elseif (is_numeric($key)) {
                if (! in_array($value, $base, true)) {
                    $base[] = $value;
                }
            } else {
                $base[$key] = $value;
            }
        }

        return $base;
    }

    /**
     * @param array<string, string|int|array> $config
     */
    private static function resolveIde(array $config): FileLinkFormatterContract
    {
        $links = [
            'textmate' => 'txmt://open?url=file://%f&line=%l',
            'macvim' => 'mvim://open?url=file://%f&line=%l',
            'emacs' => 'emacs://open?url=file://%f&line=%l',
            'sublime' => 'subl://open?url=file://%f&line=%l',
            'phpstorm' => 'phpstorm://open?file=%f&line=%l',
            'atom' => 'atom://core/open/file?filename=%f&line=%l',
            'vscode' => 'vscode://file/%f:%l',
        ];

        $ide = $config['ide'] ?? null;

        $fileFormatterPattern = ini_get('xdebug.file_link_format') ?:
            get_cfg_var('xdebug.file_link_format') ?:
            (isset($links[$ide]) ? $links[$ide] : $ide);

        $fileLinkFormatter = new NullFileLinkFormatter();

        if (null !== $fileFormatterPattern) {
            $fileLinkFormatter = new FileLinkFormatter($fileFormatterPattern);
        }

        return $fileLinkFormatter;
    }
}
