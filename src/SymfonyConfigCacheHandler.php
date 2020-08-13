<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent;

use Psr\Container\ContainerInterface;
use Symfony\Component\Config\ConfigCache;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

final class SymfonyConfigCacheHandler implements CacheHandlerInterface
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
     * @var ConfigCache
     */
    private $configCache;

    public function __construct(string $name, string $cacheDirPath, bool $debug = false)
    {
        $this->name = $this->translate($name);
        $this->cacheDirPath = $cacheDirPath;
        $this->configCache = new ConfigCache($this->getFilePath(), $debug);
    }

    public function getFromCache(): ?ContainerInterface
    {
        if ($this->configCache->isFresh()) {
            return require_once $this->configCache->getPath();
        }
        return null;
    }

    public function cache(ContainerBuilder $containerBuilder): void
    {
        $dumper = new PhpDumper($containerBuilder);
        $this->configCache->write(
            $dumper->dump(['class' => $this->name]),
            $containerBuilder->getResources()
        );
    }

    private function getFilePath(): string
    {
        return rtrim($this->cacheDirPath, '/') . '/' . $this->name . '.php';
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
