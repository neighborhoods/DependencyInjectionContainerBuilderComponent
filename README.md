# Neighborhoods Container Builder

## Basic example of usage

```php
$container = (new \Neighborhoods\DependencyInjectionContainerBuilderComponent\TinyContainerBuilder())
    ->setContainerBuilder(new \Symfony\Component\DependencyInjection\ContainerBuilder())
    ->setRootPath(dirname(__DIR__))
    ->addSourcePath('src/ComponentName')
    ->addSourcePath('src/Prefab5')
    ->addSourcePath('fab/ComponentName')
    ->makePublic(SomeRepository::class)
    ->addCompilerPass(new \Symfony\Component\DependencyInjection\Compiler\AnalyzeServiceReferencesPass())
    ->addCompilerPass(new \Symfony\Component\DependencyInjection\Compiler\InlineServiceDefinitionsPass())
    ->build();
```

* `setContainerBuilder`: the setter takes instance of `\Symfony\Component\DependencyInjection\ContainerBuilder`.
    It's possible to supply non-empty container.
* `setRootPath`: takes the path to the project root (where `src` and `fab` folders are located)
* `addSourcePath`: takes the path to a folder containing definitions for Container Builder
* `makePublic`: takes the service URI (usually class name) and makes it public
* `makeAllPublic`: makes all services public
* `addCompilerPass`: takes instance of `\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface` and
    supplies it to the `addCompilerPass` method of ContainerBuilder.
* `build`: creates and returns an instance of `\Psr\Container\ContainerInterface`

## With Cache

If container needs to be cached, a `\Neighborhoods\DependencyInjectionContainerBuilderComponent\CacheHandlerInterface`
can be supplied through `setCacheHandler`.

```php
$cacheHandler = (new \Neighborhoods\DependencyInjectionContainerBuilderComponent\SymfonyConfigCacheHandler\Builder())
    ->setName('ContainerName')
    ->setCacheDirPath(dirname(__DIR__) . '/data/cache')
    ->setDebug(true)
    ->build();
$container = (new \Neighborhoods\DependencyInjectionContainerBuilderComponent\TinyContainerBuilder())
    // ... 
    ->setCacheHandler($cacheHandler)
    ->build();
```

* `setName`: takes the name of the cached Container class
* `setCacheDirPath`: takes the path to directory where container file is going to be stored (absolute)
* `setDebug`: takes a boolean flag whether "debug mode" should be on or off. When debug mode is on, cache is going
    to "listen" for the changes in source configuration files. If any change is introduced, cache would be considered
    invalid, and a new one will be generated and stored. It's advised to use `true` only for development.

## Compatibility

This Container Builder supports unix-like path.
