Twig Markdown Extension
=======================

[![Build Status](https://secure.travis-ci.org/aptoma/twig-markdown.png?branch=master)](http://travis-ci.org/aptoma/twig-markdown)
[![Coverage Status](https://img.shields.io/coveralls/aptoma/twig-markdown.svg)](https://coveralls.io/r/aptoma/twig-markdown)

Twig Markdown extension provides a new filter and a tag to allow parsing of
content as Markdown in [Twig][1] templates.

This extension could be integrated with several Markdown parser as it provides an interface, which allows you to customize your Markdown parser.

### Supported parsers

 * [michelf/php-markdown](https://github.com/michelf/php-markdown) (+ MarkdownExtra)
 * [league/commonmark](http://commonmark.thephpleague.com/)
 * [KnpLabs/php-github-api](https://github.com/KnpLabs/php-github-api)
 * [erusev/parsedown](https://github.com/erusev/parsedown)

## Features

 * Filter support `{{ "# Heading Level 1"|markdown }}`
 * Tag support `{% markdown %}{% endmarkdown %}`

When used as a tag, the indentation level of the first line sets the default indentation level for the rest of the tag content.
From this indentation level, all same indentation or outdented levels text will be transformed as regular text.

This feature allows you to write your Markdown content at any indentation level without caring of Markdown internal transformation:

```php
<div>
    <h1 class="someClass">{{ title }}</h1>

    {% markdown %}
    This is a list that is indented to match the context around the markdown tag:

    * List item 1
    * List item 2
        * Sub List Item
            * Sub Sub List Item

    The following block will be transformed as code, as it is indented more than the
    surrounding content:

        $code = "good";

    {% endmarkdown %}

</div>
```

## Installation

Run the composer command to install the latest stable version:

```bash
composer require aptoma/twig-markdown
```

Or update your `composer.json`:

```json
{
    "require": {
        "aptoma/twig-markdown": "~1.1"
    }
}
```

You can choose to provide your own Markdown engine, although we recommend
using [michelf/php-markdown](https://github.com/michelf/php-markdown):

```bash
composer require michelf/php-markdown ~1.3
```

```json
{
    "require": {
        "michelf/php-markdown": "~1.3"
    }
}
```

You may also use the [PHP League CommonMark engine](http://commonmark.thephpleague.com/):

```bash
composer require league/commonmark ~0.5
```

```json
{
    "require": {
        "league/commonmark": "~0.5"
    }
}
```

## Usage

### Twig Extension

The Twig extension provides the `markdown` tag and filter support.

Assuming that you are using [composer](http://getcomposer.org) autoloading,
add the extension to the Twig environment:

```php

use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\Extension\MarkdownEngine;

$engine = new MarkdownEngine\MichelfMarkdownEngine();

$twig->addExtension(new MarkdownExtension($engine));
```

### Twig Token Parser

The Twig token parser provides the `markdown` tag only!

```php
use Aptoma\Twig\Extension\MarkdownEngine;
use Aptoma\Twig\TokenParser\MarkdownTokenParser;

$engine = new MarkdownEngine\MichelfMarkdownEngine();

$twig->addTokenParser(new MarkdownTokenParser($engine));
```

### GitHub Markdown Engine

`MarkdownEngine\GitHubMarkdownEngine` provides an interface to GitHub's markdown engine using their public API via [`KnpLabs\php-github-api`][2]. To reduce API calls, rendered documents are hashed and cached in the filesystem. You can pass a GitHub repository and the path to be used for caching to the constructor:

```php
use Aptoma\Twig\Extension\MarkdownEngine;

$engine = new MarkdownEngine\GitHubMarkdownEngine(
    'aptoma/twig-markdown', // The GitHub repository to use as a context
    true,                   // Whether to use GitHub's Flavored Markdown (GFM)
    '/tmp/markdown-cache',  // Path on filesystem to store rendered documents
);
```

In order to authenticate the API client (for instance), it's possible to pass an own instance of `\GitHub\Client` instead of letting the engine create one itself:

```php
$client = new \GitHub\Client;
$client->authenticate('GITHUB_CLIENT_ID', 'GITHUB_CLIENT_SECRET', \Github\Client::AUTH_URL_CLIENT_ID);

$engine = new MarkdownEngine\GitHubMarkdownEngine('aptoma/twig-markdown', true, '/tmp/markdown-cache', $client);
```

### Using a different Markdown parser engine

If you want to use a different Markdown parser, you need to create an adapter
that implements `Aptoma\Twig\Extension\MarkdownEngineInterface.php`. Have
a look at `Aptoma\Twig\Extension\MarkdownEngine\MichelfMarkdownEngine` for an
example.

## Tests

The test suite uses PHPUnit:

    $ phpunit

## License

Twig Markdown Extension is licensed under the MIT license.

[1]: http://twig.sensiolabs.org
[2]: https://github.com/knplabs/php-github-api
