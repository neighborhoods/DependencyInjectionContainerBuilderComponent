<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent;

use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

interface ContainerBuilderInterface
{
    public function makePublic(string $service): ContainerBuilderInterface;

    public function setRootPath(string $root): ContainerBuilderInterface;

    public function addSourcePath(string $path): ContainerBuilderInterface;

    public function addCompilerPass(CompilerPassInterface $compilerPass): ContainerBuilderInterface;

    public function build(): ContainerInterface;

    public function getInternalContainer(): \Symfony\Component\DependencyInjection\ContainerBuilder;

    public function setCacheHandler(CacheHandlerInterface $cacheHandler): ContainerBuilderInterface;
}
