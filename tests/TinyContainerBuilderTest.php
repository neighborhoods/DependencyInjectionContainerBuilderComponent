<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent\Test;

use Neighborhoods\DependencyInjectionContainerBuilderComponent\TinyContainerBuilder;
use org\bovigo\vfs\vfsStream;

class TinyContainerBuilderTest extends \PHPUnit\Framework\TestCase
{
    /**
     * @var \org\bovigo\vfs\vfsStreamDirectory
     */
    private $root;

    protected function setUp(): void
    {
        $this->root = vfsStream::setup('root');
    }

    public function testAddSourcePathWithoutRoot(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('When relative path is provided root should be set first.');

        $builder = new TinyContainerBuilder();
        $builder->addSourcePath('some/relative/path');
    }

    public function testAddSourcePathAbsoluteDirectory(): void
    {
        mkdir(vfsStream::url('root/somedir'));
        $url = vfsStream::url('root/somedir/somefile');
        touch($url);

        $builder = new TinyContainerBuilder();
        $builder->addSourcePath($url);

        $reflection = new \ReflectionClass(TinyContainerBuilder::class);
        $prop = $reflection->getProperty('paths');
        $prop->setAccessible(true);
        $actual = $prop->getValue($builder);

        $this->assertSame(['vfs://root/somedir/somefile'], $actual);
    }

    public function testAddSourcePathRelativeDir(): void
    {
        mkdir(vfsStream::url('root/somedir'));
        $url = vfsStream::url('root/somedir/somefile.service.yml');
        touch($url);

        $builder = new TinyContainerBuilder();
        $builder->setRootPath(vfsStream::url('root'));
        $builder->addSourcePath('somedir');

        $reflection = new \ReflectionClass(TinyContainerBuilder::class);
        $prop = $reflection->getProperty('paths');
        $prop->setAccessible(true);
        $actual = $prop->getValue($builder);

        $this->assertSame(['vfs://root/somedir/somefile.service.yml'], $actual);
    }

    public function testAddSourceInvalidPath(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Provided path is not a valid pathname:');
        mkdir(vfsStream::url('root/somedir'));

        $builder = new TinyContainerBuilder();
        $builder->setRootPath(vfsStream::url('root'));
        $builder->addSourcePath('somedir/somefile');
    }
}
