<?php

return [
    'cache:warmup:aop' => [
        'class' => \Bachi\AOP\Command\CacheWarmupCommand::class,
    ],
    'debug:advisor' => [
        'class' => \Go\Console\Command\DebugAdvisorCommand::class
    ],
    'debug:aspect' => [
        'class' => \Go\Console\Command\DebugAspectCommand::class,
    ],
    'debug:weaving' => [
        'class' => \Go\Console\Command\DebugWeavingCommand::class
    ]
];
