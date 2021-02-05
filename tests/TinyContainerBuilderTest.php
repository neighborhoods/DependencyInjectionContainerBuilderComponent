<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent\Test;

use Neighborhoods\DependencyInjectionContainerBuilderComponent\ContainerBuilderInterface;
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

        self::assertContainerPaths(['vfs://root/somedir/somefile'], $builder);
    }

    public function testAddSourcePathRelativeDir(): void
    {
        mkdir(vfsStream::url('root/somedir'));
        $url = vfsStream::url('root/somedir/somefile.service.yml');
        touch($url);

        $builder = new TinyContainerBuilder();
        $builder->setRootPath(vfsStream::url('root'));
        $builder->addSourcePath('somedir');

        self::assertContainerPaths(['vfs://root/somedir/somefile.service.yml'], $builder);
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

    public function testExcludeSourcePathWithoutRoot(): void
    {
        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('When relative path is provided root should be set first.');

        $builder = new TinyContainerBuilder();
        $builder->excludeSourcePath('some/relative/path');
    }

    public function testExcludeNotAddedSourcePath(): void
    {
        mkdir(vfsStream::url('root/somedir'));
        $url = vfsStream::url('root/somedir/somefile');
        touch($url);

        $builder = new TinyContainerBuilder();
        $builder->excludeSourcePath($url);

        self::assertContainerPaths([], $builder);
    }

    public function testExcludeAddedSourcePath(): void
    {
        mkdir(vfsStream::url('root/somedir'));
        $url = vfsStream::url('root/somedir/somefile');
        touch($url);

        $builder = new TinyContainerBuilder();
        $builder->addSourcePath($url);
        $builder->excludeSourcePath($url);

        self::assertContainerPaths([], $builder);
    }

    public function testExcludeParentDirectory(): void
    {
        mkdir(vfsStream::url('root/somedir'));
        $url = vfsStream::url('root/somedir/somefile');
        touch($url);

        $builder = new TinyContainerBuilder();
        $builder->addSourcePath($url);
        $builder->excludeSourcePath($url . '/..');

        self::assertContainerPaths([], $builder);
    }

    public function testDoesntExcludeSiblingSourcePath(): void
    {
        mkdir(vfsStream::url('root/somedir'));
        $url = vfsStream::url('root/somedir/somefile');
        touch($url);
        $excludePath = vfsStream::url('root/somedir/some');
        touch($excludePath);

        $builder = new TinyContainerBuilder();
        $builder->addSourcePath($url);
        $builder->excludeSourcePath($excludePath);

        self::assertContainerPaths(['vfs://root/somedir/somefile'], $builder);
    }

    public function testExcludeSourceInvalidPath(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Provided path is not a valid pathname:');
        mkdir(vfsStream::url('root/somedir'));

        $builder = new TinyContainerBuilder();
        $builder->setRootPath(vfsStream::url('root'));
        $builder->excludeSourcePath('somedir/somefile');
    }

    private static function assertContainerPaths(
        array $expectedPaths,
        ContainerBuilderInterface $containerBuilder
    ): void {
        $reflection = new \ReflectionClass(TinyContainerBuilder::class);
        $pathsProperty = $reflection->getProperty('paths');
        $pathsProperty->setAccessible(true);
        $actualPaths = $pathsProperty->getValue($containerBuilder);

        self::assertSame($expectedPaths, $actualPaths);
    }
}
