<?php

declare(strict_types=1);

namespace Tests\Application;

use NunoMaduro\PhpInsights\Application\ConfigResolver;
use NunoMaduro\PhpInsights\Domain\Contracts\FileLinkFormatter;
use NunoMaduro\PhpInsights\Domain\Exceptions\PresetNotFound;
use NunoMaduro\PhpInsights\Domain\LinkFormatter\NullFileLinkFormatter;
use PHPUnit\Framework\TestCase;
use SlevomatCodingStandard\Sniffs\Commenting\DocCommentSpacingSniff;

final class ConfigResolverTest extends TestCase
{
    /**
     * @var string
     */
    private $baseFixturePath;

    public function setUp(): void
    {
        parent::setUp();

        $this->baseFixturePath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'Fixtures' . DIRECTORY_SEPARATOR . 'ConfigResolver' . DIRECTORY_SEPARATOR;
    }

    public function testGuessDirectoryWithoutComposer(): void
    {
        $preset = ConfigResolver::guess($this->baseFixturePath);
        self::assertSame('default', $preset);
    }

    public function testGuessComposerWithoutRequire(): void
    {
        $preset = ConfigResolver::guess($this->baseFixturePath . 'ComposerWithoutRequire');
        self::assertSame('default', $preset);
    }

    public function testGuessSymfony(): void
    {
        $preset = ConfigResolver::guess($this->baseFixturePath . 'ComposerSymfony');
        self::assertSame('symfony', $preset);
    }

    public function testGuessLaravel(): void
    {
        $preset = ConfigResolver::guess($this->baseFixturePath . 'ComposerLaravel');
        self::assertSame('laravel', $preset);
    }

    public function testGuessYii(): void
    {
        $preset = ConfigResolver::guess($this->baseFixturePath . 'ComposerYii');
        self::assertSame('yii', $preset);
    }

    public function testGuessMagento2(): void
    {
        $preset = ConfigResolver::guess($this->baseFixturePath . 'ComposerMagento2');
        self::assertSame('magento2', $preset);
    }

    public function testGuessDrupal(): void
    {
        $preset = ConfigResolver::guess($this->baseFixturePath . 'ComposerDrupal');
        self::assertSame('drupal', $preset);
    }

    public function testResolvedConfigIsCorrectlyMerged(): void
    {
        $config = [
            'exclude' => [
                'my/path',
            ],
            'config' => [
                DocCommentSpacingSniff::class => [
                    'linesCountBetweenDifferentAnnotationsTypes' => 2
                ]
            ]
        ];

        $finalConfig = ConfigResolver::resolve($config, $this->baseFixturePath . 'ComposerWithoutRequire');

        self::assertArrayHasKey('exclude', $finalConfig);
        self::assertArrayHasKey('config', $finalConfig);
        self::assertContains('my/path', $finalConfig['exclude']);
        // assert we don't replace the first value
        self::assertContains('bower_components', $finalConfig['exclude']);
        self::assertArrayHasKey(DocCommentSpacingSniff::class, $finalConfig['config']);
        // assert we replace the config value
        self::assertEquals(
            2,
            $finalConfig['config'][DocCommentSpacingSniff::class]['linesCountBetweenDifferentAnnotationsTypes']
        );
    }

    public function testUnknownPresetThrowException(): void
    {
        self::expectException(PresetNotFound::class);
        self::expectExceptionMessage('UnknownPreset not found');

        $config = ['preset' => 'UnknownPreset'];

        ConfigResolver::resolve($config, $this->baseFixturePath . 'ComposerWithoutRequire');
    }

    /**
     * @dataProvider provideValidIde
     */
    public function testResolveValidIde(string $ide): void
    {
        $config = ['ide' => $ide];

        $config = ConfigResolver::resolve($config, $this->baseFixturePath);

        self::assertArrayHasKey('fileLinkFormatter', $config);
        self::assertInstanceOf(FileLinkFormatter::class, $config['fileLinkFormatter']);
        self::assertNotInstanceOf(NullFileLinkFormatter::class, $config['fileLinkFormatter']);
    }

    public function testResolveWithoutIde():void
    {
        $config = [];

        $config = ConfigResolver::resolve($config, $this->baseFixturePath);

        self::assertArrayHasKey('fileLinkFormatter', $config);
        self::assertInstanceOf(FileLinkFormatter::class, $config['fileLinkFormatter']);
        self::assertInstanceOf(NullFileLinkFormatter::class, $config['fileLinkFormatter']);
    }

    public function testResolveWithIdePattern(): void
    {
        $config = ['ide' => 'myide://file=%f&line=%l'];

        $config = ConfigResolver::resolve($config, $this->baseFixturePath);

        self::assertArrayHasKey('fileLinkFormatter', $config);
        self::assertInstanceOf(FileLinkFormatter::class, $config['fileLinkFormatter']);
        self::assertNotInstanceOf(NullFileLinkFormatter::class, $config['fileLinkFormatter']);
    }

    /**
     * @return array<string, array<string>>
     */
    public function provideValidIde(): array
    {
        return [
            'Sublime Text' => ['sublime'],
            'PhpStorm' => ['phpstorm'],
            'Visual studio Code' => ['vscode'],
            'Textmate' => ['textmate'],
            'Emacs' => ['textmate'],
            'Atom' => ['atom'],
            'Macvim' => ['macvim'],
        ];
    }
}
