<?php

$header = <<<EOF
AJGL Breakpoint Twig Extension Component

Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>

For the full copyright and license information, please view the LICENSE
file that was distributed with this source code.
EOF;

Symfony\CS\Fixer\Contrib\HeaderCommentFixer::setHeader($header);

return Symfony\CS\Config\Config::create()
    ->setUsingCache(true)
    // use default SYMFONY_LEVEL and extra fixers:
    ->fixers(array(
        '-psr0',
        'header_comment',
        'newline_after_open_tag',
        'ordered_use',
        'phpdoc_order',
        'strict',
        'strict_param',
    ))
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->in(__DIR__.'/src')
            ->in(__DIR__.'/tests')
    )
;
