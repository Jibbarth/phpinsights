<?php

declare(strict_types=1);

namespace NunoMaduro\PhpInsights\Domain;

use function count;
use function max;

/**
 * @internal
 */
final class Collector
{
    /**
     * @var string
     */
    private $dir;

    /**
     * @var int
     */
    private $commentLines = 0;

    /**
     * @var int
     */
    private $logicalLines = 0;

    /**
     * @var int
     */
    private $functionLines = 0;

    /**
     * @var string[]
     */
    private $files = [];

    /**
     * @var string[]
     */
    private $directories = [];

    /**
     * @var string[]
     */
    private $concreteNonFinalClasses = [];

    /**
     * @var string[]
     */
    private $concreteFinalClasses = [];

    /**
     * @var string[]
     */
    private $abstractClasses = [];

    /**
     * @var string[]
     */
    private $traits = [];

    /**
     * @var string[]
     */
    private $globalConstants = [];

    /**
     * @var int
     */
    private $interfaces = 0;

    /**
     * @var string[]
     */
    private $namespaces = [];

    /**
     * @var int
     */
    private $complexity = 0;

    /**
     * @var int
     */
    private $totalMethodComplexity = 0;

    /**
     * @var int[]
     */
    private $methodComplexity = [];

    /**
     * @var array<string, float>
     */
    private $classComplexity = [];

    /**
     * @var int
     */
    private $classConstants = 0;

    /**
     * @var array<string, int>
     */
    private $methodLines = [];

    /**
     * @var array<string, float>
     */
    private $classLines = [];

    /**
     * @var int
     */
    private $staticAttributeAccesses = 0;

    /**
     * @var int
     */
    private $superGlobalVariableAccesses = 0;

    /**
     * @var string[]
     */
    private $possibleConstantAccesses = [];

    /**
     * @var int
     */
    private $globalVariableAccesses = 0;

    /**
     * @var int
     */
    private $nonStaticMethodCalls = 0;

    /**
     * @var int
     */
    private $nonStaticAttributeAccesses = 0;

    /**
     * @var int
     */
    private $anonymousFunctions = 0;

    /**
     * @var array<string, string[]>
     */
    private $namedFunctions = [];

    /**
     * @var int
     */
    private $publicMethods = 0;

    /**
     * @var int
     */
    private $staticMethods = 0;

    /**
     * @var int
     */
    private $nonStaticMethods = 0;

    /**
     * @var int
     */
    private $protectedMethods = 0;

    /**
     * @var int
     */
    private $privateMethods = 0;

    /**
     * @var int
     */
    private $staticMethodCalls = 0;

    /**
     * @var string
     */
    private $currentFilename = '';

    /**
     * @var int
     */
    private $currentClassComplexity = 0;

    /**
     * @var int
     */
    private $currentClassLines = 0;

    /**
     * @var int
     */
    private $currentMethodComplexity = 0;

    /**
     * @var int
     */
    private $currentMethodLines = 0;

    /**
     * @var array<string, string>
     */
    private $globalFunctions = [];

    /**
     * Creates a new instance of the Collector.
     */
    public function __construct(string $dir)
    {
        $this->dir = $dir;
    }

    public function addFile(string $filename): void
    {
        $filename = str_replace($this->dir . '/', '', $filename);

        $this->files[$filename] = $filename;
        $this->directories[] = \dirname($filename);
        $this->directories = array_unique($this->directories);
        $this->currentFilename = $filename;
    }

    public function incrementCommentLines(int $number): void
    {
        $this->commentLines += $number;
    }

    public function incrementLogicalLines(): void
    {
        $this->logicalLines++;
    }

    public function currentClassReset(): void
    {
        if ($this->currentClassComplexity > 0) {
            $this->classComplexity[$this->currentFilename] = $this->currentClassComplexity;
            $this->classLines[$this->currentFilename] = $this->currentClassLines;
        }

        $this->currentClassComplexity = 0;
        $this->currentClassLines = 0;
    }

    public function currentClassIncrementComplexity(): void
    {
        $this->currentClassComplexity++;
    }

    public function currentClassIncrementLines(): void
    {
        $this->currentClassLines++;
    }

    public function currentMethodStart(): void
    {
        $this->currentMethodComplexity = 1;
        $this->currentMethodLines = 0;
    }

    public function currentMethodIncrementComplexity(): void
    {
        $this->currentMethodComplexity++;
        $this->totalMethodComplexity++;
    }

    public function currentMethodIncrementLines(): void
    {
        $this->currentMethodLines++;
    }

    public function currentMethodStop(string $name): void
    {
        $this->methodComplexity[] = $this->currentMethodComplexity;
        $this->methodLines[$this->currentFilename . ':' . $name] = $this->currentMethodLines;
    }

    public function incrementFunctionLines(): void
    {
        $this->functionLines++;
    }

    public function incrementComplexity(): void
    {
        $this->complexity++;
    }

    public function addPossibleConstantAccesses(string $name): void
    {
        $this->possibleConstantAccesses[] = $name;
    }

    public function addGlobalFunctions(int $line, string $name): void
    {
        $this->globalFunctions[$this->currentFilename . ':' . $line] = $name;
    }

    public function incrementGlobalVariableAccesses(): void
    {
        $this->globalVariableAccesses++;
    }

    public function incrementSuperGlobalVariableAccesses(): void
    {
        $this->superGlobalVariableAccesses++;
    }

    public function incrementNonStaticAttributeAccesses(): void
    {
        $this->nonStaticAttributeAccesses++;
    }

    public function incrementStaticAttributeAccesses(): void
    {
        $this->staticAttributeAccesses++;
    }

    public function incrementNonStaticMethodCalls(): void
    {
        $this->nonStaticMethodCalls++;
    }

    public function incrementStaticMethodCalls(): void
    {
        $this->staticMethodCalls++;
    }

    public function addNamespace(string $namespace): void
    {
        $this->namespaces[] = $namespace;
        $this->namespaces = array_flip(array_flip($this->namespaces));
    }

    public function incrementInterfaces(): void
    {
        $this->interfaces++;
    }

    public function addAbstractClass(string $name): void
    {
        $this->abstractClasses[] = $name;
    }

    public function addConcreteFinalClass(string $name): void
    {
        $this->concreteFinalClasses[] = $name;
    }

    public function addConcreteNonFinalClass(string $name): void
    {
        $this->concreteNonFinalClasses[] = $name;
    }

    public function incrementNonStaticMethods(): void
    {
        $this->nonStaticMethods++;
    }

    public function incrementStaticMethods(): void
    {
        $this->staticMethods++;
    }

    public function incrementPublicMethods(): void
    {
        $this->publicMethods++;
    }

    public function incrementProtectedMethods(): void
    {
        $this->protectedMethods++;
    }

    public function incrementPrivateMethods(): void
    {
        $this->privateMethods++;
    }

    public function addNamedFunctions(string $name): void
    {
        if (! array_key_exists($this->currentFilename, $this->namedFunctions)) {
            $this->namedFunctions[$this->currentFilename] = [];
        }

        $this->namedFunctions[$this->currentFilename][] = $name;
    }

    public function incrementAnonymousFunctions(): void
    {
        $this->anonymousFunctions++;
    }

    public function incrementClassConstants(): void
    {
        $this->classConstants++;
    }

    public function addGlobalConstant(string $name): void
    {
        $this->globalConstants[$this->currentFilename] = $name;
    }

    public function incrementTraits(): void
    {
        if ($this->currentFilename !== null) {
            $this->traits[] = $this->currentFilename;
        }
    }

    /**
     * Returns the analysed dir.
     */
    public function getDir(): string
    {
        return $this->dir;
    }

    public function getLines(): int
    {
        return $this->getCommentLines()
            + $this->getFunctionLines()
            + $this->getClassLines()
            + $this->getNotInClassesOrFunctions();
    }

    public function getCommentLines(): int
    {
        return $this->commentLines;
    }

    /**
     * @return array<string, string>
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @return string[]
     */
    public function getDirectories(): array
    {
        return $this->directories;
    }

    /**
     * @return string[]
     */
    public function getGlobalConstants(): array
    {
        return $this->globalConstants;
    }

    /**
     * @return string[]
     */
    public function getTraits(): array
    {
        return $this->traits;
    }

    public function getClassLines(): int
    {
        return (int) $this->getSum($this->classLines);
    }

    /**
     * @return array<string, float>
     */
    public function getPerClassLines(): array
    {
        return $this->classLines;
    }

    public function getCurrentFilename(): string
    {
        return $this->currentFilename;
    }

    public function getCurrentClassComplexity(): int
    {
        return $this->currentClassComplexity;
    }

    public function getCurrentClassLines(): int
    {
        return $this->currentClassLines;
    }

    public function getCurrentMethodComplexity(): int
    {
        return $this->currentMethodComplexity;
    }

    public function getCurrentMethodLines(): int
    {
        return $this->currentMethodLines;
    }

    public function getLogicalLines(): int
    {
        return $this->logicalLines;
    }

    public function getMethodComplexity(): int
    {
        return $this->totalMethodComplexity;
    }

    public function getClassConstants(): int
    {
        return $this->classConstants;
    }

    public function getFunctionLines(): int
    {
        return $this->functionLines;
    }

    /**
     * @return array<string, int>
     */
    public function getMethodLines(): array
    {
        return $this->methodLines;
    }

    /**
     * @return array<string, string>
     */
    public function getGlobalFunctions(): array
    {
        return $this->globalFunctions;
    }

    public function getStaticAttributeAccesses(): int
    {
        return $this->staticAttributeAccesses;
    }

    public function getComplexity(): int
    {
        return $this->complexity;
    }

    /**
     * @return string[]
     */
    public function getPossibleConstantAccesses(): array
    {
        return $this->possibleConstantAccesses;
    }

    public function getGlobalVariableAccesses(): int
    {
        return $this->globalVariableAccesses;
    }

    public function getNonStaticMethodCalls(): int
    {
        return $this->nonStaticMethodCalls;
    }

    public function getNonStaticAttributeAccesses(): int
    {
        return $this->nonStaticAttributeAccesses;
    }

    public function getAnonymousFunctions(): int
    {
        return $this->anonymousFunctions;
    }

    /**
     * @return array<string, string[]>
     */
    public function getNamedFunctions(): array
    {
        return $this->namedFunctions;
    }

    public function getPublicMethods(): int
    {
        return $this->publicMethods;
    }

    public function getStaticMethods(): int
    {
        return $this->staticMethods;
    }

    public function getNonStaticMethods(): int
    {
        return $this->nonStaticMethods;
    }

    /**
     * @return string[]
     */
    public function getConcreteNonFinalClasses(): array
    {
        return $this->concreteNonFinalClasses;
    }

    /**
     * @return string[]
     */
    public function getConcreteFinalClasses(): array
    {
        return $this->concreteFinalClasses;
    }

    /**
     * @return string[]
     */
    public function getNamespaces(): array
    {
        return $this->namespaces;
    }

    public function getProtectedMethods(): int
    {
        return $this->protectedMethods;
    }

    public function getPrivateMethods(): int
    {
        return $this->privateMethods;
    }

    public function getStaticMethodCalls(): int
    {
        return $this->staticMethodCalls;
    }

    public function getInterfaces(): int
    {
        return $this->interfaces;
    }

    /**
     * @return string[]
     */
    public function getAbstractClasses(): array
    {
        return $this->abstractClasses;
    }

    public function getSuperGlobalVariableAccesses(): int
    {
        return $this->superGlobalVariableAccesses;
    }

    public function getNonCommentLines(): int
    {
        return $this->getLines() - $this->getCommentLines();
    }

    /**
     * @return float|int
     */
    public function getAverageClassLength()
    {
        return $this->getAverage($this->classLines);
    }

    public function getMaximumClassLength(): int
    {
        return (int) $this->getMaximum($this->classLines);
    }

    public function getAverageMethodLength(): int
    {
        return (int) $this->getAverage($this->methodLines);
    }

    public function getMaximumMethodLength(): int
    {
        return (int) $this->getMaximum($this->methodLines);
    }

    public function getAverageFunctionLength(): int
    {
        return (int) $this->divide($this->getFunctionLines(), $this->getFunctions());
    }

    public function getNotInClassesOrFunctions(): int
    {
        return $this->getLogicalLines() - $this->getClassLines() - $this->getFunctionLines();
    }

    public function getAverageComplexityPerLogicalLine(): float
    {
        return $this->divide($this->getLogicalLines(), $this->getComplexity());
    }

    public function getAverageComplexityPerClass(): float
    {
        return $this->getAverage($this->classComplexity);
    }

    /**
     * Return.
     *
     * @return array<string, float>
     */
    public function getClassComplexity(): array
    {
        return $this->classComplexity;
    }

    public function getMaximumClassComplexity(): int
    {
        return (int) $this->getMaximum($this->getClassComplexity());
    }

    public function getAverageComplexityPerMethod(): float
    {
        return $this->getAverage($this->methodComplexity);
    }

    public function getMaximumMethodComplexity(): float
    {
        return $this->getMaximum($this->methodComplexity);
    }

    public function getGlobalAccesses(): int
    {
        return $this->getGlobalConstantAccesses() + $this->getGlobalVariableAccesses() + $this->getSuperGlobalVariableAccesses();
    }

    public function getGlobalConstantAccesses(): int
    {
        return count(\array_intersect($this->possibleConstantAccesses, $this->globalConstants));
    }

    public function getAttributeAccesses(): int
    {
        return $this->getNonStaticAttributeAccesses() + $this->getStaticAttributeAccesses();
    }

    public function getMethodCalls(): int
    {
        return $this->getNonStaticMethodCalls() + $this->getStaticMethodCalls();
    }

    public function getClasses(): int
    {
        return count($this->getAbstractClasses()) + count($this->getConcreteNonFinalClasses()) + count($this->getConcreteFinalClasses());
    }

    public function getMethods(): int
    {
        return $this->getNonStaticMethods() + $this->getStaticMethods();
    }

    public function getFunctions(): int
    {
        return count($this->getNamedFunctions()) + $this->getAnonymousFunctions();
    }

    public function getConstants(): int
    {
        return count($this->getGlobalConstants()) + $this->getClassConstants();
    }

    /**
     * @param array<float> $array
     */
    private function getAverage(array $array): float
    {
        return $this->divide($this->getSum($array), $this->getCount($array));
    }

    /**
     * @param array<string, float|int> $array
     */
    private function getCount(array $array): int
    {
        return count($array);
    }

    /**
     * Returns the sum value from the given array.
     *
     * @param array<string, float|int> $array
     *
     * @return float|int
     */
    private function getSum(array $array)
    {
        return array_sum($array);
    }

    /**
     * Returns the maximum value from the given array.
     *
     * @param array<string, float|int> $array
     *
     * @return float|int
     */
    private function getMaximum(array $array)
    {
        return (bool) count($array) ? max($array) : 0;
    }

    private function divide(float $x, float $y): float
    {
        return $y !== 0 ? $x / $y : 0;
    }
}
