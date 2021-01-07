<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent;

use Neighborhoods\DependencyInjectionContainerBuilderComponent\SymfonyConfigCacheHandler\Dumper\BuilderInterface;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

final class SymfonyConfigCacheHandler implements CacheHandlerInterface
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var ConfigCacheInterface
     */
    private $configCache;
    /**
     * @var BuilderInterface
     */
    private $dumperBuilder;

    public function getFromCache(): ContainerInterface
    {
        if ($this->getConfigCache()->isFresh()) {
            require_once $this->getConfigCache()->getPath();

            return new $this->name;
        }
        throw new \LogicException('No cache existing for Container Builder');
    }

    public function cache(ContainerBuilder $containerBuilder): void
    {
        $this->getConfigCache()->write(
            $this->getDumperBuilder()->setContainerBuilder($containerBuilder)->build()->dump(['class' => $this->getName()]),
            $containerBuilder->getResources()
        );
    }

    public function hasInCache(): bool
    {
        return $this->getConfigCache()->isFresh();
    }

    public function getName(): string
    {
        if ($this->name === null) {
            throw new \LogicException('Name is not set');
        }

        return $this->name;
    }

    public function setName(string $name): self
    {
        if (isset($this->name)) {
            throw new \LogicException('Name is already set');
        }
        $this->name = $name;

        return $this;
    }

    public function getConfigCache(): ConfigCacheInterface
    {
        if ($this->configCache === null) {
            throw new \LogicException('Config Cache is not set');
        }

        return $this->configCache;
    }

    public function setConfigCache(ConfigCacheInterface $configCache): self
    {
        if (isset($this->configCache)) {
            throw new \LogicException('Config Cache is already set');
        }
        $this->configCache = $configCache;

        return $this;
    }

    public function getDumperBuilder(): BuilderInterface
    {
        if ($this->dumperBuilder === null) {
            throw new \LogicException('Dumper Builder is not set');
        }

        return $this->dumperBuilder;
    }

    public function setDumperBuilder(BuilderInterface $dumperBuilder): self
    {
        if (isset($this->dumperBuilder)) {
            throw new \LogicException('Dumper Builder is already set');
        }
        $this->dumperBuilder = $dumperBuilder;

        return $this;
    }
}
