<?php

require __DIR__.'/../vendor/autoload.php';

$loader = new Twig_Loader_Array(array(
    'uniqid' => 'Hi, I am unique: {{ php_uniqid() }}.',
    'floor'  => 'And {{ php_floor(7.7) }} is floor of 7.7.',
));

$twig = new Twig_Environment($loader);
$twig->addExtension(new Umpirsky\Twig\Extension\PhpFunctionExtension());

echo $twig->render('uniqid') . PHP_EOL;
echo $twig->render('floor') . PHP_EOL;
