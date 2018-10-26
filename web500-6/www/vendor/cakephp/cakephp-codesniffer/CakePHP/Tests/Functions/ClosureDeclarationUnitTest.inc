<?php
namespace Beakman;

class TraitUser
{

    use FunctionsTrait;

    /**
     * [doThing description]
     *
     * @param callable $callback The description.
     * @return void
     */
    public function doThing(callable $callback)
    {
        $visitor = function($expression) use (&$visitor, $callback) {
            echo 'It fails';
        };
        $visitor = function ($expression)use(&$visitor, $callback) {
            echo 'It works';
        };
        $visitor($this);
    }
}

$foo = 'bar';
$bar = 'foo';

$zum = function()use ($foo, $bar) {
    return $foo;
};

$zum = function ()use ($foo, $bar) {
    return $foo;
};