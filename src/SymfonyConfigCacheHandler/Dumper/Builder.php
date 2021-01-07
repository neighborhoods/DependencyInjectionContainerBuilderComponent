<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent\SymfonyConfigCacheHandler\Dumper;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\DumperInterface;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

class Builder implements BuilderInterface
{
    /**
     * @var ContainerBuilder
     */
    private $containerBuilder;

    public function build(): DumperInterface
    {
        return new PhpDumper($this->getContainerBuilder());
    }

    public function setContainerBuilder(ContainerBuilder $containerBuilder): BuilderInterface
    {
        if (isset($this->containerBuilder)) {
            throw new \LogicException('Container Builder is already set');
        }
        $this->containerBuilder = $containerBuilder;

        return $this;
    }

    public function getContainerBuilder(): ContainerBuilder
    {
        if ($this->containerBuilder === null) {
            throw new \LogicException('Container Builder is not set');
        }

        return $this->containerBuilder;
    }
}
