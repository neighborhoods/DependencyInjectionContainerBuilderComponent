<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent\SymfonyConfigCacheHandler;

use Neighborhoods\DependencyInjectionContainerBuilderComponent\CacheHandlerInterface;
use Neighborhoods\DependencyInjectionContainerBuilderComponent\SymfonyConfigCacheHandler;
use Symfony\Component\Config\ConfigCache;

class Builder implements BuilderInterface
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

    public function build(): CacheHandlerInterface
    {
        return (new SymfonyConfigCacheHandler())
            ->setName($this->getName())
            ->setConfigCache(new ConfigCache($this->getFilePath(), $this->isDebug()))
            ->setDumperBuilder(new SymfonyConfigCacheHandler\Dumper\Builder());
    }

    public function setName(string $name): BuilderInterface
    {
        if (isset($this->name)) {
            throw new \LogicException('Name is already set');
        }
        $this->name = $this->translate($name);

        return $this;
    }

    public function setCacheDirPath(string $path): BuilderInterface
    {
        if (isset($this->cacheDirPath)) {
            throw new \LogicException('Cache Dir is already set');
        }
        if (!\file_exists($path) || !is_dir($path)) {
            throw new \RuntimeException(\sprintf('Cache directory is not accessible or invalid: %s', $path));
        }
        $this->cacheDirPath = $path;

        return $this;
    }

    public function setDebug(bool $debug): BuilderInterface
    {
        if (isset($this->debug)) {
            throw new \LogicException('Debug is already set');
        }
        $this->debug = $debug;

        return $this;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        if ($this->name === null) {
            throw new \LogicException('Name has not been set');
        }

        return $this->name;
    }

    /**
     * @return string
     */
    public function getCacheDirPath(): string
    {
        if ($this->cacheDirPath === null) {
            throw new \LogicException('Cache Dir has not been set');
        }

        return $this->cacheDirPath;
    }

    /**
     * @return bool
     */
    public function isDebug(): bool
    {
        if ($this->debug === null) {
            throw new \LogicException('Debug has not been set');
        }

        return $this->debug;
    }

    private function getFilePath(): string
    {
        return rtrim($this->getCacheDirPath(), '/') . '/' . $this->name . '.php';
    }

    private function translate(string $name): string
    {
        return take($name)
            ->pipe('preg_replace', ...['/[^a-zA-Z0-9 ]/', '', PIPED_VALUE])
            ->pipe('ucwords')
            ->pipe('str_replace', ...[' ', '', PIPED_VALUE])
            ->get();
    }
}
