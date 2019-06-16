<?php

namespace Bachi\AOP\Autoload;

use Doctrine\Common\Annotations\AnnotationRegistry;
use Go\Core\AspectContainer;
use Go\Instrument\FileSystem\Enumerator;
use Go\Instrument\PathResolver;
use Go\Instrument\Transformer\FilterInjectorTransformer;
use TYPO3\ClassAliasLoader\ClassAliasLoader;

final class AopClassLoader
{
    /**
     * @var bool
     */
    private static $wasInitialized;
    /**
     * @var Enumerator
     */
    private $fileEnumerator;
    /**
     * @var array
     */
    private $cacheState;
    /**
     * @var array
     */
    private $options;
    /**
     * @var ClassAliasLoader
     */
    private $original;

    /**
     * Constructs an wrapper for the composer loader
     *
     * @param ClassAliasLoader $original Instance of current loader
     * @param AspectContainer $container Instance of the container
     * @param array $options Configuration options
     */
    public function __construct(ClassAliasLoader $original, AspectContainer $container, array $options = [])
    {
        $this->options  = $options;
        $this->original = $original;

        $prefixes     = $original->getPrefixes();
        $excludePaths = $options['excludePaths'];

        if (!empty($prefixes)) {
            // Let's exclude core dependencies from that list
            if (isset($prefixes['Dissect'])) {
                $excludePaths[] = $prefixes['Dissect'][0];
            }
            if (isset($prefixes['Doctrine\\Common\\Annotations\\'])) {
                $excludePaths[] = substr($prefixes['Doctrine\\Common\\Annotations\\'][0], 0, -16);
            }
        }

        $fileEnumerator       = new Enumerator($options['appDir'], $options['includePaths'], $excludePaths);
        $this->fileEnumerator = $fileEnumerator;
        $this->cacheState     = $container->get('aspect.cache.path.manager')->queryCacheState();
    }


    /**
     * Initialize aspect autoloader
     *
     * Replaces original composer autoloader with wrapper
     *
     * @param array $options Aspect kernel options
     * @param AspectContainer $container
     *
     * @return bool was initialization sucessful or not
     */
    public static function init(array $options, AspectContainer $container)
    {
        $loaders = spl_autoload_functions();

        foreach ($loaders as &$loader) {
            $loaderToUnregister = $loader;
            if (is_array($loader) && ($loader[0] instanceof ClassAliasLoader)) {
                $originalLoader = $loader[0];
                $loader[0] = new AopClassLoader($loader[0], $container, $options);
                $loader[1] = 'loadClass';

                // Configure library loader for doctrine annotation loader
                AnnotationRegistry::registerLoader(function($class) use ($originalLoader) {
                    $originalLoader->loadClass($class);

                    return class_exists($class, false);
                });

                self::$wasInitialized = true;
            }

            spl_autoload_unregister($loaderToUnregister);
        }

        foreach ($loaders as $loader) {
            spl_autoload_register($loader);
        }

        return self::$wasInitialized;
    }

    /**
     * Autoload a class by it's name
     *
     * @param string $class Name of the class to load
     */
    public function loadClass($class)
    {
        $class = $this->original->getClassNameForAlias($class);

        $file = $this->findFile($class);
        if ($file !== false) {
            require $file;
        }
    }

    /**
     * Finds either the path to the file where the class is defined,
     * or gets the appropriate php://filter stream for the given class.
     *
     * @param string $class
     * @return string|false The path/resource if found, false otherwise.
     */
    public function findFile($class)
    {
        static $isAllowedFilter = null, $isProduction = false;
        if (!$isAllowedFilter) {
            $isAllowedFilter = $this->fileEnumerator->getFilter();
            $isProduction    = !$this->options['debug'];
        }

        $file = $this->original->findFile($class);

        if ($file !== false) {
            $file = PathResolver::realpath($file)?:$file;
            $cacheState = isset($this->cacheState[$file]) ? $this->cacheState[$file] : null;
            if ($cacheState && $isProduction) {
                $file = $cacheState['cacheUri'] ?: $file;
            } elseif ($isAllowedFilter(new \SplFileInfo($file))) {
                // can be optimized here with $cacheState even for debug mode, but no needed right now
                $file = FilterInjectorTransformer::rewrite($file);
            }
        }

        return $file;
    }
}
