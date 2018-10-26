AjglBreakpointTwigExtension
===========================

The AjglBreakpointTwigExtension component allows you set breakpoints in twig templates.

[![Build Status](https://travis-ci.org/ajgarlag/AjglBreakpointTwigExtension.png?branch=master)](https://travis-ci.org/ajgarlag/AjglBreakpointTwigExtension)
[![Latest Stable Version](https://poser.pugx.org/ajgl/breakpoint-twig-extension/v/stable.png)](https://packagist.org/packages/ajgl/breakpoint-twig-extension)
[![Latest Unstable Version](https://poser.pugx.org/ajgl/breakpoint-twig-extension/v/unstable.png)](https://packagist.org/packages/ajgl/breakpoint-twig-extension)
[![Total Downloads](https://poser.pugx.org/ajgl/breakpoint-twig-extension/downloads.png)](https://packagist.org/packages/ajgl/breakpoint-twig-extension)
[![Montly Downloads](https://poser.pugx.org/ajgl/breakpoint-twig-extension/d/monthly.png)](https://packagist.org/packages/ajgl/breakpoint-twig-extension)
[![Daily Downloads](https://poser.pugx.org/ajgl/breakpoint-twig-extension/d/daily.png)](https://packagist.org/packages/ajgl/breakpoint-twig-extension)
[![License](https://poser.pugx.org/ajgl/breakpoint-twig-extension/license.png)](https://packagist.org/packages/ajgl/breakpoint-twig-extension)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/ajgarlag/AjglBreakpointTwigExtension/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/ajgarlag/AjglBreakpointTwigExtension/?branch=master)
[![Code Coverage](https://scrutinizer-ci.com/g/ajgarlag/AjglBreakpointTwigExtension/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/ajgarlag/AjglBreakpointTwigExtension/?branch=master)
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/e0f1276d-6ded-4a20-9b3f-1a7c77a92015/mini.png)](https://insight.sensiolabs.com/projects/e0f1276d-6ded-4a20-9b3f-1a7c77a92015)
[![StyleCI](https://styleci.io/repos/53512207/shield)](https://styleci.io/repos/53512207)

This component requires the [Xdebug] PHP extension to be installed.


Installation
------------

To install the latest stable version of this component, open a console and execute the following command:
```
$ composer require ajgl/breakpoint-twig-extension
```


Usage
-----

The first step is to register the extension into the twig environment
```php
/* @var $twig Twig_Environment */
$twig->addExtension(new Ajgl\Twig\Extension\BreakpointExtension());
```

Once registered, you can call the new `breakpoint` function:
```twig
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>title</title>
  </head>
  <body>
    {{ breakpoint() }}
  </body>
</html>
```

Once stopped, your debugger will allow you to inspect the `$environment` and `$context` variables.

### Function arguments

Any argument passed to the twig function will be added to the `$arguments` array, so you can inspect it easily.

```twig
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>title</title>
  </head>
  <body>
    {{ breakpoint(app.user, app.session) }}
  </body>
</html>
```

Symfony Bundle
--------------

If you want to use this extension in your Symfony application, you can enable the
Symfony Bundle included in this package:

```php
// app/AppKernel.php
if (in_array($this->getEnvironment(), array('dev', 'test'), true)) {
    $bundles[] = new Ajgl\Twig\Extension\SymfonyBundle\AjglBreakpointTwigExtensionBundle();
}
```

This bundle will register the twig extension automatically. So, once enabled, you
can insert the `breakpoint` twig function in your templates.


License
-------

This component is under the MIT license. See the complete license in the [LICENSE] file.


Reporting an issue or a feature request
---------------------------------------

Issues and feature requests are tracked in the [Github issue tracker].


Author Information
------------------

Developed with ♥ by [Antonio J. García Lagar].

If you find this component useful, please add a ★ in the [GitHub repository page] and/or the [Packagist package page].

[Xdebug]: https://xdebug.org/
[LICENSE]: LICENSE
[Github issue tracker]: https://github.com/ajgarlag/AjglBreakpointTwigExtension/issues
[Antonio J. García Lagar]: http://aj.garcialagar.es
[GitHub repository page]: https://github.com/ajgarlag/AjglBreakpointTwigExtension
[Packagist package page]: https://packagist.org/packages/ajgl/breakpoint-twig-extension
