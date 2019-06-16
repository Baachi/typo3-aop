<?php

declare(strict_types=1);

namespace Bachi\AOP\Kernel;

use Bachi\AOP\Autoload\AopClassLoader;
use Go\Aop\Features;
use Go\Core\AspectContainer;
use Go\Core\AspectKernel;
use Go\Instrument\ClassLoading\SourceTransformingLoader;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

class TYPO3AspectKernel extends AspectKernel
{
    private static $aspects = [];

    public static function registerAspect(string $class) : void
    {
        static::$aspects[] = $class;
    }

    public function init(array $options = [])
    {
        if ($this->wasInitialized) {
            return;
        }

        $this->options = $this->normalizeOptions($options);
        define('AOP_ROOT_DIR', $this->options['appDir']);
        define('AOP_CACHE_DIR', $this->options['cacheDir']);

        /** @var $container AspectContainer */
        $container = $this->container = new $this->options['containerClass'];
        $container->set('kernel', $this);
        $container->set('kernel.interceptFunctions', $this->hasFeature(Features::INTERCEPT_FUNCTIONS));
        $container->set('kernel.options', $this->options);

        SourceTransformingLoader::register();

        foreach ($this->registerTransformers() as $sourceTransformer) {
            SourceTransformingLoader::addTransformer($sourceTransformer);
        }

        // Register kernel resources in the container for debug mode
        if ($this->options['debug']) {
            $this->addKernelResourcesToContainer($container);
        }

        AopClassLoader::init($this->options, $container);

        // Register all AOP configuration in the container
        $this->configureAop($container);

        $this->wasInitialized = true;
    }

    /**
     * Configure an AspectContainer with advisors, aspects and pointcuts
     *
     * @param AspectContainer $container
     *
     * @return void
     */
    protected function configureAop(AspectContainer $container)
    {
        /** @var ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        foreach (static::$aspects as $class) {
            $container->registerAspect($objectManager->get($class));
        }
    }
}
