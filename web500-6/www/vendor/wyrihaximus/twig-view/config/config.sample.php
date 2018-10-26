<?php

return [
    'WyriHaximus' => [
        'TwigView' => [
            'environment' => [
                // Anything you would pass into \Twig_Environment to overwrite the default settings, see: http://twig.sensiolabs.org/doc/api.html#environment-options
            ],
            'markdown' => [
                'engine' => 'engine', // See https://github.com/aptoma/twig-markdown#installation
            ],
            'flags' => [
                'potentialDangerous' => false,
            ],
        ],
    ],
];
