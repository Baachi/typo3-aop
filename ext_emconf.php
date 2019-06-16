
<?php
$EM_CONF[$_EXTKEY] = [
    'title' => 'AOP',
    'description' => 'TYPO3 Extension to enable aop programming for your extensions.',
    'category' => 'be',
    'author' => 'Markus Bachmann',
    'author_email' => 'markus.bachmann@bachi.biz',
    'state' => 'stable',
    'clearCacheOnLoad' => true,
    'version' => '1.0.0',
    'constraints' => [
        'depends' => [
            'typo3' => '8.7.13-9.5.99',
        ],
        'conflicts' => [],
    ],
];
