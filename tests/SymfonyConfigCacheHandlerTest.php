<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent\Test;

use Neighborhoods\DependencyInjectionContainerBuilderComponent\SymfonyConfigCacheHandler;
use Neighborhoods\DependencyInjectionContainerBuilderComponent\TinyContainerBuilder;
use org\bovigo\vfs\vfsStream;
use Psr\Container\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

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
        $cache = new SymfonyConfigCacheHandler('test', vfsStream::url('root'), false);

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
        $cache = new SymfonyConfigCacheHandler('test', vfsStream::url('root'), true);

        // no Metadata file, cache is considered stalled
        $actual = $cache->getFromCache();
        $this->assertNull($actual);
    }

    public function testCache(): void
    {
        $cache = new SymfonyConfigCacheHandler('j1o2k3e', vfsStream::url('root'));

        $builder = new ContainerBuilder();
        $builder->compile();
        $cache->cache($builder);

        $this->assertTrue(\file_exists(vfsStream::url('root/Joke.php')));
    }
}
