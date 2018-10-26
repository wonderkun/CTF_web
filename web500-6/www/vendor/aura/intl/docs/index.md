# Aura.Intl

The Aura.Intl package provides internationalization (I18N) tools, specifically
package-oriented per-locale message translation.

## Getting Started

You can instantiate _TranslatorLocator_ object from _TranslatorLocatorFactory_
as below

```php
<?php
use Aura\Intl\TranslatorLocatorFactory;

$factory = new TranslatorLocatorFactory();
$translators = $factory->newInstance();
?>
```

Alternatively, we can add the Aura.Intl package `/path/to/Aura.Intl/src` to
our autoloader and build a translator locator manually:

```php
<?php
use Aura\Intl\PackageLocator;
use Aura\Intl\FormatterLocator;
use Aura\Intl\TranslatorFactory;
use Aura\Intl\TranslatorLocator;

return new TranslatorLocator(
    new PackageLocator,
    new FormatterLocator([
        'basic' => function () { return new \Aura\Intl\BasicFormatter; },
        'intl'  => function () { return new \Aura\Intl\IntlFormatter; },
    ]),
    new TranslatorFactory,
    'en_US'
);
?>
```

## Setting Localized Messages For A Package

We can set localized messages for a package through the `PackageLocator` object
from the translator locator. We create a new `Package` with messages and place
it into the locator as a callable. The messages take the form of a message key and
and message string.

```php
<?php
use Aura\Intl\Package;

// get the package locator
$packages = $translators->getPackages();

// place into the locator for Vendor.Package
$packages->set('Vendor.Package', 'en_US', function() {
    // create a US English message set
    $package = new Package;
    $package->setMessages([
        'FOO' => 'The text for "foo."',
        'BAR' => 'The text for "bar."',
    ]);
    return $package;
});

// place into the locator for a Vendor.Package
$packages->set('Vendor.Package', 'pt_BR', function() {
    // a Brazilian Portuguese message set
    $package = new Package;
    $package->setMessages([
        'FOO' => 'O texto de "foo".',
        'BAR' => 'O texto de "bar".',
    ]);
    return $package;
});
?>
```


## Setting The Default Locale

We can set the default locale for translations using the `setLocale()` method:

```php
<?php
$translators->setLocale('pt_BR');
?>
```

## Getting A Localized Message

Now that the translator locator has messages and a default locale, we can get
an individual package translator. The package translator is suitable for
injection into another class, or for standalone use.

```php
<?php
// recall that the default locale is pt_BR
$translator = $translators->get('Vendor.Package');
echo $translator->translate('FOO'); // 'O texto de "foo".'
?>
```

You can get a translator for a non-default locale as well:

```php
<?php
$translator = $translators->get('Vendor.Package', 'en_US');
echo $translator->translate('FOO'); // 'The text for "foo."'
?>
```


## Replacing Message Tokens With Values

We often need to use dynamic values in translated messages. First, the
message string needs to have a token placeholder for the dynamic value:

```php
<?php
// get the packages out of the translator locator
$packages = $translators->getPackages();

$packages->set('Vendor.Dynamic', 'en_US', function() {

    // US English messages
    $package = new Package;
    $package->setMessages([
        'PAGE' => 'Page {page} of {pages} pages.',
    ]);
    return $package;
});

$packages->set('Vendor.Dynamic', 'pt_BR', function() {
    // Brazilian Portuguese messages
    $package = new Package;
    $package->setMessages([
        'PAGE' => 'Página {page} de {pages} páginas.',
    ]);
    return $package;
});
?>
```

Then, when we translate the message, we provide an array of tokens and
replacement values.  These will be interpolated into the message string.

```php
<?php
// recall that the default locale is pt_BR
$translator = $translators->get('Vendor.Dynamic');
echo $translator->translate('PAGE', [
    'page' => 1,
    'pages' => 1,
]); // 'Página 1 de 1 páginas.'
?>
```

## Pluralized Messages

Usually, we need to use different messages when a value is singular or plural.
The `BasicFormatter` is not capable of presenting different messages based on
different token values. The `IntlFormatter` *is* capable, but the PHP
[`intl`](http://php.net/intl) extension must be loaded to take advantage of
it, and we must specify the `'intl'` formatter for the package in the catalog.

When using the `IntlFormatter`, we can build our message strings to present
singular or plural messages, as in the following example:

```php
<?php
// get the packages out of the translator locator
$packages = $translators->getCatalog();

// get the Vendor.Dynamic package en_US locale and set
// US English messages with pluralization. note the use
// of # instead of {pages} herein; using the placeholder
// "inside itself" with the Intl formatter causes trouble.
$package->setMessages([
    'PAGE' => '{pages,plural,'
            . '=0{No pages.}'
            . '=1{One page only.}'
            . 'other{Page {page} of # pages.}'
            . '}'
]);

// use the 'intl' formatter for this package and locale
$package->setFormatter('intl');

// now that we have added the pluralizable messages,
// get the US English translator for the package
$translator = $translators->get('Vendor.Dynamic', 'en_US');

// zero translation
echo $translator->translate('PAGE', [
    'page' => 0,
    'pages' => 0,
]); // 'No pages.'

// singular translation
echo $translator->translate('PAGE', [
    'page' => 1,
    'pages' => 1,
]); // 'One page only.'

// plural translation
echo $translator->translate('PAGE', [
    'page' => 3,
    'pages' => 10,
]); // 'Page 3 of 10 pages.'
?>
```

Note that you can use other tokens within a pluralized token string to build
more complex messages. For more information, see the following:

<http://icu-project.org/apiref/icu4j/com/ibm/icu/text/MessageFormat.html>
