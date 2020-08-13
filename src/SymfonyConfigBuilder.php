<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent;

class SymfonyConfigBuilder
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var string
     */
    private $cacheDirPath;
    /**
     * @var bool
     */
    private $debug;

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getCacheDirPath(): string
    {
        return $this->cacheDirPath;
    }

    /**
     * @param string $cacheDirPath
     */
    public function setCacheDirPath(string $cacheDirPath): self
    {
        $this->cacheDirPath = $cacheDirPath;

        return $this;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        return $this->debug;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): self
    {
        $this->debug = $debug;

        return $this;
    }

    public function build(): SymfonyConfigCacheHandler
    {
        return new SymfonyConfigCacheHandler($this->getName(), $this->getCacheDirPath(), $this->isDebug());
    }
}
