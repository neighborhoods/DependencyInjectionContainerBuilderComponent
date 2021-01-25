<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PassConfig;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Filesystem\Filesystem;
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
    private $containerBuilder;
    /**
     * @var string[]
     */
    private $paths = [];
    /**
     * @var array
     */
    private $compilerPasses = [];
    /**
     * @var string[]
     */
    private $publicServices = [];
    /**
     * @var CacheHandlerInterface
     */
    private $cacheHandler;

    public function setRootPath(string $root): ContainerBuilderInterface
    {
        if (isset($this->rootPath)) {
            throw new \LogicException('Root path is already set');
        }
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
            $path = rtrim($this->rootPath, '/') . '/' . $path;
        }
        if (!\file_exists($path)) {
            throw new \RuntimeException(\sprintf('Provided path is not a valid pathname: %s', $path));
        }
        if (\is_dir($path)) {
            $serviceDefinitions = (new Finder())->name('*.service.yml')->files()->in($path);
            foreach ($serviceDefinitions as $file) {
                $this->paths[] = $file->getPathname();
            }
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
        $this->compilerPasses[] = [
            'pass' => $compilerPass,
            'type' => $type,
            'priority' => $priority,
        ];

        return $this;
    }

    public function build(): ContainerInterface
    {
        if ($this->hasCacheHandler() && $this->getCacheHandler()->hasInCache()) {
            return $this->getCacheHandler()->getFromCache();
        }
        $loader = new YamlFileLoader($this->getInternalContainer(), new FileLocator());
        foreach ($this->paths as $file) {
            $loader->import($file);
        }
        foreach ($this->compilerPasses as $data) {
            $this->getInternalContainer()->addCompilerPass($data['pass'], $data['type'], $data['priority']);
        }
        foreach ($this->publicServices as $publicService) {
            $this->getInternalContainer()->getDefinition($publicService)->setPublic(true);
        }
        $this->getInternalContainer()->compile(true);
        if ($this->hasCacheHandler()) {
            $this->getCacheHandler()->cache($this->containerBuilder);
        }

        return $this->getInternalContainer();
    }

    public function getInternalContainer(): \Symfony\Component\DependencyInjection\ContainerBuilder
    {
        if ($this->containerBuilder === null) {
            throw new \LogicException('Container Builder is not set');
        }

        return $this->containerBuilder;
    }

    public function makePublic(string $service): ContainerBuilderInterface
    {
        $this->publicServices[] = $service;

        return $this;
    }

    public function setCacheHandler(CacheHandlerInterface $cacheHandler): ContainerBuilderInterface
    {
        if ($this->hasCacheHandler()) {
            throw new \LogicException('Cache Handler has already been set');
        }
        $this->cacheHandler = $cacheHandler;

        return $this;
    }

    public function getCacheHandler(): CacheHandlerInterface
    {
        if (!$this->hasCacheHandler()) {
            throw new \LogicException('Cache Handler is not set');
        }

        return $this->cacheHandler;
    }

    public function hasCacheHandler(): bool
    {
        return $this->cacheHandler !== null;
    }

    public function setContainerBuilder(ContainerBuilder $containerBuilder): self
    {
        if (isset($this->containerBuilder)) {
            throw new \LogicException('Container Builder is already set');
        }
        $this->containerBuilder = $containerBuilder;

        return $this;
    }

    private function isAbsolute(string $path)
    {
        return (new Filesystem())->isAbsolutePath($path);
    }

    public function excludeSourcePath(string $excludePath): ContainerBuilderInterface
    {
        if (!$this->isAbsolute($excludePath)) {
            if (!isset($this->rootPath)) {
                throw new \LogicException(
                    \sprintf('When relative path is provided root should be set first. Privided: %s', $excludePath)
                );
            }
            $excludePath = rtrim($this->rootPath, '/') . '/' . $excludePath;
        }
        if (!\file_exists($excludePath)) {
            throw new \RuntimeException(\sprintf('Provided exclude path is not a valid pathname: %s', $excludePath));
        }

        // The extra slash at the end prevents exclusion of sibling paths starting with exclude path name
        // For example /usr/bin/php shouldn't exclude /usr/bin/php7.4
        $excludePath = $this->removeRelativePathParts($excludePath) . '/';
        $this->paths = array_filter($this->paths, function (string $path) use ($excludePath) {
            return 0 !== stripos($this->removeRelativePathParts($path) . '/', $excludePath);
        });

        return $this;
    }

    private function removeRelativePathParts(string $path): string
    {
        $result = [];

        foreach (explode('/', trim($path, '/')) as $segment) {
            if ('..' === $segment) {
                array_pop($result);
            } elseif ('.' !== $segment && '' !== $segment) {
                $result[] = $segment;
            }
        }

        return implode('/', $result);
    }
}
