<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

interface CacheHandlerInterface
{
    public function hasInCache(): bool;

    public function getFromCache(): ContainerInterface;

    public function cache(ContainerBuilder $containerBuilder): void;
}
