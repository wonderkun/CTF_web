<?php

namespace Umpirsky\Twig\Extension;

use Twig_Extension;
use Twig_SimpleFunction;
use BadFunctionCallException;

class PhpFunctionExtension extends Twig_Extension
{
    private $functions = array(
        'uniqid',
        'floor',
        'ceil',
        'addslashes',
        'chr',
        'chunk_​split',
        'convert_​uudecode',
        'crc32',
        'crypt',
        'hex2bin',
        'md5',
        'sha1',
        'strpos',
        'strrpos',
        'ucwords',
        'wordwrap',
        'gettype',
    );

    public function __construct(array $functions = array())
    {
        if ($functions) {
            $this->allowFunctions($functions);
        }
    }

    public function getFunctions()
    {
        $twigFunctions = array();

        foreach ($this->functions as $function) {
            $twigFunctions[] = new Twig_SimpleFunction($function, $function);
        }

        return $twigFunctions;
    }

    public function allowFunction($function)
    {
        $this->functions[] = $function;
    }

    public function allowFunctions(array $functions)
    {
        $this->functions = $functions;
    }

    public function getName()
    {
        return 'php_function';
    }
}
