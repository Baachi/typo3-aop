<?php

namespace Bachi\AOP\Command;

use Bachi\AOP\Kernel\TYPO3AspectKernel;
use Go\Console\Command\CacheWarmupCommand as BaseCacheWarmupCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CacheWarmupCommand extends BaseCacheWarmupCommand
{
    protected function configure()
    {
        parent::configure();
        $arguments = $this->getDefinition()->getArguments();
        unset($arguments['loader']);
        $this->getDefinition()->setArguments($arguments);
    }

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->aspectKernel = TYPO3AspectKernel::getInstance();
    }

    protected function loadAspectKernel(InputInterface $input, OutputInterface $output)
    {
        // NOOP
    }
}
