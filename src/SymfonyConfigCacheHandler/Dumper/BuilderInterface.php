<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent\SymfonyConfigCacheHandler\Dumper;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\DumperInterface;

interface BuilderInterface
{
    public function build(): DumperInterface;

    public function setContainerBuilder(ContainerBuilder $containerBuilder): BuilderInterface;
}
