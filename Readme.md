# TYPO3 AOP Extension

This extension enables you to use [Aspect Oriented Programming](https://en.wikipedia.org/wiki/Aspect-oriented_programming)
in TYPO3. Behind the scenes we use the [goaop](https://github.com/goaop/framework) framework, which
is stable and fast.

For more information look at the documentation on [goaop documentation](https://github.com/goaop/framework).

## Why do you need this?

Do you ever need to extend the functionality of a extension or the core itself
but no hook or signal slot is in place? With this extension you can hook into
every class, no matter what you need.

It gives you to be more flexibility, to find the perfect solution for your
case.

## Installation

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this package:

```bash
$ composer require bachi/typo3-aop:@dev
```

This command requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

**Attention**: Installtion through the TYPO3 Extension Installer is not supported,
due it's limitation of the autoloading process and dependency management.

## How to use

First of all you need to create an `Aspect`:

```php

namespace Acme\Demo\Aspect\LoggingAspect

use Go\Aop\Aspect;
use Go\Lang\Annotation\Before;

final class LoggingAspect implements Aspect
{
    /**
     * @Before("execution(public Example->*(*))")
     */
    public function beforeMethodExecution(MethodInvocation $invocation)
    {
        // do some stuff.
    }
}
```

Please be aware, that this aspect implements the `Aspect` interface!

Now we need to register our aspect into the new container:

```php
# ext_localconf.php
\Baachi\GoAOP\Kernel\TYPO3AspectKernel::registerAspect(\Acme\Demo\Aspect\LoggingAspect::class);
```  

Done! Easy right?

## Tuning for production

This extension have an `debug` mode, ensure that this option is disabled
in your production environment. 

Also register an cache warmup command. It's strongly encouraged to run this command
in your deployment routine.

```
$ php -dmemory_limit=-1 vendor/bin/typo3 cache:warmup:aop
```

This command consumes a lot of memory!
