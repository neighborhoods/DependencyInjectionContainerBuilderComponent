<?php

declare(strict_types=1);

namespace Neighborhoods\DependencyInjectionContainerBuilderComponent\Test;

use Neighborhoods\DependencyInjectionContainerBuilderComponent\TinyContainerBuilder;
use PHPUnit\Framework\TestCase;
use stdClass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use org\bovigo\vfs\vfsStream;
use Throwable;

class TinyContainerBuilderTest extends TestCase
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
        $this->expectExceptionMessage('When relative path is provided root should be set.');

        $builder = new TinyContainerBuilder();
        $builder->setContainerBuilder(new ContainerBuilder());
        $builder->addSourcePath('some/relative/path');
        $builder->build();
    }

    public function testAddSourcePathAbsoluteDirectory(): void
    {
        mkdir(vfsStream::url('root/somedir'));
        $directoryPath = vfsStream::url('root/somedir');
        $url = vfsStream::url('root/somedir/somefile.service.yml');
        file_put_contents($url, "services:\n  stdClass:\n    class: stdClass");

        $builder = new TinyContainerBuilder();
        $builder->setContainerBuilder(new ContainerBuilder());
        $builder->setRootPath(vfsStream::url('root'));
        $builder->addSourcePath($directoryPath);
        $builder->makeAllPublic();
        $container = $builder->build();

        $service = $container->get('stdClass');
        self::assertEquals(stdClass::class, get_class($service));
    }

    public function testAddSourcePathRelativeDir(): void
    {
        mkdir(vfsStream::url('root/somedir'));
        $url = vfsStream::url('root/somedir/somefile.service.yml');
        file_put_contents($url, "services:\n  stdClass:\n    class: stdClass");

        $builder = new TinyContainerBuilder();
        $builder->setContainerBuilder(new ContainerBuilder());
        $builder->setRootPath(vfsStream::url('root'));
        $builder->addSourcePath('somedir');
        $builder->makeAllPublic();
        $container = $builder->build();

        $service = $container->get('stdClass');
        self::assertEquals(stdClass::class, get_class($service));
    }

    public function testAddSourcePathAbsoluteFile(): void
    {
        mkdir(vfsStream::url('root/somedir'));
        $filePath = vfsStream::url('root/somedir/somefile.service.yml');
        file_put_contents($filePath, "services:\n  stdClass:\n    class: stdClass");

        $builder = new TinyContainerBuilder();
        $builder->setContainerBuilder(new ContainerBuilder());
        $builder->setRootPath(vfsStream::url('root'));
        $builder->addSourcePath($filePath);
        $builder->makeAllPublic();
        $container = $builder->build();

        $service = $container->get('stdClass');
        self::assertEquals(stdClass::class, get_class($service));
    }

    public function testAddSourcePathRelativePath(): void
    {
        mkdir(vfsStream::url('root/somedir'));
        $url = vfsStream::url('root/somedir/somefile.service.yml');
        file_put_contents($url, "services:\n  stdClass:\n    class: stdClass");

        $builder = new TinyContainerBuilder();
        $builder->setContainerBuilder(new ContainerBuilder());
        $builder->setRootPath(vfsStream::url('root'));
        $builder->addSourcePath('somedir/somefile.service.yml');
        $builder->makeAllPublic();
        $container = $builder->build();

        $service = $container->get('stdClass');
        self::assertEquals(stdClass::class, get_class($service));
    }

    public function testAddSourcePathInvalidYaml(): void
    {
        mkdir(vfsStream::url('root/somedir'));
        $filePath = vfsStream::url('root/somedir/somefile.service.yml');
        file_put_contents($filePath, "services: stdClass: class");

        $this->expectException(Throwable::class);

        $builder = new TinyContainerBuilder();
        $builder->setContainerBuilder(new ContainerBuilder());
        $builder->setRootPath(vfsStream::url('root'));
        $builder->addSourcePath($filePath);
        $builder->makeAllPublic();
        $builder->build();
    }

    public function testAddSourceInvalidPath(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Provided path is not a valid pathname:');
        mkdir(vfsStream::url('root/somedir'));

        $builder = new TinyContainerBuilder();
        $builder->setContainerBuilder(new ContainerBuilder());
        $builder->setRootPath(vfsStream::url('root'));
        $builder->addSourcePath('somedir/somefile');
        $builder->build();
    }
}
