<h3 align="center">
    <a href="https://github.com/umpirsky">
        <img src="https://farm2.staticflickr.com/1709/25098526884_ae4d50465f_o_d.png" />
    </a>
</h3>
<p align="center">
  <a href="https://github.com/umpirsky/Symfony-Upgrade-Fixer">symfony upgrade fixer</a> &bull;
  <a href="https://github.com/umpirsky/Twig-Gettext-Extractor">twig gettext extractor</a> &bull;
  <a href="https://github.com/umpirsky/wisdom">wisdom</a> &bull;
  <a href="https://github.com/umpirsky/centipede">centipede</a> &bull;
  <a href="https://github.com/umpirsky/PermissionsHandler">permissions handler</a> &bull;
  <a href="https://github.com/umpirsky/Extraload">extraload</a> &bull;
  <a href="https://github.com/umpirsky/Gravatar">gravatar</a> &bull;
  <a href="https://github.com/umpirsky/locurro">locurro</a> &bull;
  <a href="https://github.com/umpirsky/country-list">country list</a> &bull;
  <a href="https://github.com/umpirsky/Transliterator">transliterator</a>
</p>

# Twig PHP Function [![Build Status](https://travis-ci.org/umpirsky/twig-php-function.svg?branch=master)](https://travis-ci.org/umpirsky/twig-php-function)

Call (almost) any PHP function from your Twig templates.

## Usage

After [registering](http://twig.sensiolabs.org/doc/advanced.html#creating-an-extension) `PhpFunctionExtension` call PHP functions from your templates like this:

```twig
Hi, I am unique: {{ uniqid() }}.

And {{ floor(7.7) }} is floor of 7.7.
```

## Extend

You can control allowed PHP functions by adding new ones like this:

```php
$extension = new Umpirsky\Twig\Extension\PhpFunctionExtension();
$extension->allowFunction('hash_hmac');
```

or restrict what functions are allowed like this:

```php
$extension = new Umpirsky\Twig\Extension\PhpFunctionExtension(['floor', 'ceil']);

```

If you think that some function should be allowed/not allowed, feel free to [raise issue](https://github.com/umpirsky/twig-php-function/issues/new) or submit a pull request.
