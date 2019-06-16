
<?php
defined('TYPO3_MODE') or die();

(function () {
    $backendConfiguration = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Configuration\ExtensionConfiguration::class)
        ->get('typo3_aop');

    $features = 0;
    if (true === $backendConfiguration['features.']['interceptFunctions']) {
        $features ^= \Go\Aop\Features::INTERCEPT_FUNCTIONS;
    }
    if (true === $backendConfiguration['features.']['interceptInitalizationz']) {
        $features ^= \Go\Aop\Features::INTERCEPT_INITIALIZATIONS;
    }
    if (true === $backendConfiguration['features.']['interceptIncludes']) {
        $features ^= \Go\Aop\Features::INTERCEPT_INCLUDES;
    }
    if (true === $backendConfiguration['features.']['prebuildCache']) {
        $features ^= \Go\Aop\Features::PREBUILT_CACHE;
    }

    /** @var \TYPO3\CMS\Core\Package\PackageManager $packageManager */
    $packageManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Package\PackageManager::class);
    $excludePaths = $backendConfiguration['excludeDirectories'] ?? [] + [
        PATH_site. '/../vendor',
        PATH_site. '/typo3/sysext/core/Classes/Category',
    ];
    $includePaths = $backendConfiguration['includeDirectories'] ?? [];

    foreach ($packageManager->getAvailablePackages() as $availablePackage) {
        $includePaths[] = $availablePackage->getPackagePath().'Classes';
    }

    $applicationAspectKernel = \Bachi\AOP\Kernel\TYPO3AspectKernel::getInstance();
    $applicationAspectKernel->init([
        'debug'        => !!$backendConfiguration['debug'],
        'appDir'       => PATH_site . '/../',
        'features'     => $features,
        'cacheDir'     => PATH_site . '/../../var/cache/aop',
        'excludePaths' => $excludePaths,
        'includePaths' => $includePaths,
    ]);
})();
