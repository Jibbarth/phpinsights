<?php

declare(strict_types=1);

namespace NunoMaduro\PhpInsights\Domain\Insights;

use NunoMaduro\PhpInsights\Domain\Collector;
use NunoMaduro\PhpInsights\Domain\Contracts\Insight as InsightContract;

abstract class Insight implements InsightContract
{
    /**
     * @var \NunoMaduro\PhpInsights\Domain\Collector
     */
    protected $collector;

    /**
     * @var array<string, int|string>
     */
    protected $config;

    /**
     * Creates an new instance of the Insight.
     *
     * @param array<string, int|string> $config
     */
    final public function __construct(Collector $collector, array $config)
    {
        $this->collector = $collector;
        $this->config = $config;
    }
}
