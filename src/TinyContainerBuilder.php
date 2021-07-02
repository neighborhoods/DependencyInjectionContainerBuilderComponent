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
    private $sourcePaths = [];
    /**
     * @var array
     */
    private $compilerPasses = [];
    /**
     * @var string[]
     */
    private $publicServices = [];
    /**
     * @var boolean
     */
    private $makeAllServicesPublic = false;
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
        $this->sourcePaths[] = $path;

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
        // Get cached container if available
        if ($this->hasCacheHandler() && $this->getCacheHandler()->hasInCache()) {
            return $this->getCacheHandler()->getFromCache();
        }

        // Build service definition files
        $serviceDefinitionPathnames = [];
        foreach($this->sourcePaths as $sourcePath) {
            if (!$this->isAbsolute($sourcePath)) {
                if (!isset($this->rootPath)) {
                    throw new \LogicException(
                        \sprintf('When relative path is provided root should be set. Privided path: %s', $sourcePath)
                    );
                }
                $sourcePath = rtrim($this->rootPath, '/') . '/' . $sourcePath;
            }
            if (!\file_exists($sourcePath)) {
                throw new \RuntimeException(\sprintf('Provided path is not a valid pathname: %s', $sourcePath));
            }
            if (\is_dir($sourcePath)) {
                $serviceDefinitionFiles = (new Finder())->name('*.service.yml')->files()->in($sourcePath);
                foreach ($serviceDefinitionFiles as $file) {
                    $serviceDefinitionPathnames[] = $file->getPathname();
                }
            } else {
                $serviceDefinitionPathnames[] = $sourcePath;
            }
        }
        $serviceDefinitionPathnames = array_unique($serviceDefinitionPathnames);

        // Import service definitions from files
        $loader = new YamlFileLoader($this->getInternalContainer(), new FileLocator());
        foreach ($serviceDefinitionPathnames as $serviceDefinitionPathname) {
            $loader->import($serviceDefinitionPathname);
        }

        // Configure compiler passes
        foreach ($this->compilerPasses as $data) {
            $this->getInternalContainer()->addCompilerPass($data['pass'], $data['type'], $data['priority']);
        }

        // Configure public services
        if ($this->makeAllServicesPublic) {
            foreach ($this->getInternalContainer()->getDefinitions() as $definition) {
                $definition->setPublic(true);
            }
            foreach ($this->getInternalContainer()->getAliases() as $alias) {
                $alias->setPublic(true);
            }
        } else {
            foreach ($this->publicServices as $publicService) {
                $this->getInternalContainer()->getDefinition($publicService)->setPublic(true);
            }
        }

        // Build and cache container
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
        if ($this->makeAllServicesPublic) {
            throw new \LogicException('Container builder has already been instructed to make all services public.');
        }
        $this->publicServices[] = $service;

        return $this;
    }

    public function makeAllPublic(): ContainerBuilderInterface
    {
        if ($this->makeAllServicesPublic) {
            throw new \LogicException('Container builder has already been instructed to make all services public.');
        }
        $this->makeAllServicesPublic = true;
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
}
