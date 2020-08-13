<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Finder\Finder;

final class TinyContainerBuilder implements ContainerBuilderInterface
{
    /**
     * @var string
     */
    private $rootPath;
    /**
     * @var ContainerBuilder
     */
    private $builder;
    /**
     * @var string[]
     */
    private $paths;
    /**
     * @var string[]
     */
    private $publicServices = [];
    /**
     * @var CacheHandlerInterface
     */
    private $cacheHandler;

    public function __construct()
    {
        $this->builder = new ContainerBuilder();
    }

    public function setRootPath(string $root): ContainerBuilderInterface
    {
        $this->rootPath = $root;

        return $this;
    }

    public function addSourcePath(string $path): ContainerBuilderInterface
    {
        if (!$this->isAbsolute($path)) {
            if (!isset($this->rootPath)) {
                throw new \LogicException(
                    \sprintf('When relative path is provided root should be set first. Privided: %s', $path)
                );
            }
            $path = $this->rootPath . '/' . $path;
        }
        if (!\file_exists($path)) {
            throw new \RuntimeException(\sprintf('Provided path is not a valid pathname: %s', $path));
        }
        if (\is_dir($path)) {
            $this->paths += (new Finder())->name('**/*.service.yml')->files()->in($path);
        } else {
            $this->paths[] = $path;
        }
        $this->paths = array_unique($this->paths);

        return $this;
    }

    public function addCompilerPass(
        CompilerPassInterface $compilerPass,
        $type = PassConfig::TYPE_BEFORE_OPTIMIZATION,
        int $priority = 0
    ): ContainerBuilderInterface {
        $this->builder->addCompilerPass($compilerPass, $type, $priority);

        return $this;
    }

    public function build(): ContainerInterface
    {
        if ($this->cacheHandler && ($fromCache = $this->cacheHandler->getFromCache())) {
            return $fromCache;
        }
        $loader = new YamlFileLoader($this->builder, new FileLocator());
        foreach ($this->paths as $file) {
            $loader->import($file);
        }
        foreach ($this->publicServices as $publicService) {
            $this->builder->getDefinition($publicService)->setPublic(true);
        }
        $this->builder->compile(true);
        if ($this->cacheHandler) {
            $this->cacheHandler->cache($this->builder);
        }

        return $this->builder;
    }

    public function getInternalContainer(): \Symfony\Component\DependencyInjection\ContainerBuilder
    {
        return $this->builder;
    }

    public function makePublic(string $service): ContainerBuilderInterface
    {
        $this->publicServices[] = $service;

        return $this;
    }

    public function setCacheHandler(CacheHandlerInterface $cacheHandler): ContainerBuilderInterface
    {
        $this->cacheHandler = $cacheHandler;

        return $this;
    }

    private function isAbsolute(string $path)
    {
        return '/' === $path[0];
    }
}
