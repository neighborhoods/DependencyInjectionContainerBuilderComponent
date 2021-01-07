<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent\SymfonyConfigCacheHandler;

use Neighborhoods\DependencyInjectionContainerBuilderComponent\CacheHandlerInterface;

interface BuilderInterface
{
    public function build(): CacheHandlerInterface;

    public function setName(string $name): BuilderInterface;

    public function setCacheDirPath(string $path): BuilderInterface;

    public function setDebug(bool $debug): BuilderInterface;
}
