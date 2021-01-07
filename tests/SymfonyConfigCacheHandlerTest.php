<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent\Test;

use Neighborhoods\DependencyInjectionContainerBuilderComponent\SymfonyConfigCacheHandler;
use org\bovigo\vfs\vfsStream;
use Psr\Container\ContainerInterface;
use Symfony\Component\Config\ConfigCacheInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Dumper\PhpDumper;

class SymfonyConfigCacheHandlerTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('root');
    }

    public function testGetCacheHit(): void
    {
        $content = <<<PHP
<?php
class Test extends \Symfony\Component\DependencyInjection\Container
{
}
PHP;

        \file_put_contents(vfsStream::url('root/Test.php'), $content);
        $configCache = $this->getMockBuilder(ConfigCacheInterface::class)->getMock();
        $configCache->expects($this->once())->method('isFresh')->willReturn(true);
        $configCache->expects($this->once())->method('getPath')->willReturn(vfsStream::url('root/Test.php'));

        $cache = (new SymfonyConfigCacheHandler())->setName('test')
            ->setConfigCache($configCache);


        $actual = $cache->getFromCache();
        $this->assertInstanceOf(ContainerInterface::class, $actual);
        $this->assertInstanceOf('Test', $actual);
    }

    public function testGetCacheMiss(): void
    {
        $content = <<<PHP
<?php
class Test extends \Symfony\Component\DependencyInjection\Container
{
}
PHP;

        \file_put_contents(vfsStream::url('root/Test.php'), $content);
        $configCache = $this->getMockBuilder(ConfigCacheInterface::class)->getMock();
        $configCache->expects($this->once())->method('isFresh')->willReturn(false);
        $cache = (new SymfonyConfigCacheHandler())->setName('test')
            ->setConfigCache($configCache);


        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('No cache existing for Container Builder');

        $cache->getFromCache();
    }

    public function testCache(): void
    {
        $dumperBuilder = $this->getMockBuilder(SymfonyConfigCacheHandler\Dumper\BuilderInterface::class)->getMock();
        $dumper = $this->getMockBuilder(PhpDumper::class)->disableOriginalConstructor()->getMock();
        $dumper->expects($this->once())->method('dump')->willReturn('bla123');
        $dumperBuilder->expects($this->once())->method('build')->willReturn($dumper);
        $configCache = $this->getMockBuilder(ConfigCacheInterface::class)->getMock();
        $cache = (new SymfonyConfigCacheHandler())->setName('test')
            ->setConfigCache($configCache)
            ->setDumperBuilder($dumperBuilder);

        $builder = $this->getMockBuilder(ContainerBuilder::class)->getMock();
        $builder->expects($this->once())->method('getResources')->willReturn([]);

        $dumperBuilder->expects($this->once())->method('setContainerBuilder')->with($builder)->willReturnSelf();
        $configCache->expects($this->once())->method('write')->with('bla123', []);

        $cache->cache($builder);
    }
}
