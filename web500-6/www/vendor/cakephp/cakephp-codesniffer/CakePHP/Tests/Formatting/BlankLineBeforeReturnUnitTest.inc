<?php
return [
    'foo' => 'bar'
];

function invalidFunctionReturnOne()
{
    echo "";
    return null;
}

function invalidFunctionReturnOne()
{
    if(true) {
        echo "";
    }
    return null;
}

/**
 * [validFunctionReturnOne description]
 *
 * @return null
 */
function validFunctionReturnOne()
{
    return null;
}

/**
 * [validFunctionReturnTwo description]
 *
 * @return null|bool
 */
function validFunctionReturnTwo()
{
    if ($a) {
        return true;
    }

    return null;
}

/**
 * [validFunctionReturnThree description]
 *
 * @return null
 */
function validFunctionReturnThree()
{
    echo "";

    return null;
}

/**
 * [validFunctionReturnFour description]
 *
 * @return null
 */
function validFunctionReturnFour()
{
    // comment
    return null;
}

/**
 * [validFunctionReturnFive description]
 *
 * @return null
 */
function validFunctionReturnFive()
{
    /**
     * multi-line
     */
    return null;
}

/**
 * [validFunctionReturnSix description]
 *
 * @return null|bool
 */
function validFunctionReturnSix()
{
    switch ($condition) {
        case 'foo':
            return true;
        default:
            return false;
    }
}

/**
 * [validFunctionReturnSeven description]
 *
 * @return null
 */
function validFunctionReturnSeven()
{
    /** @var Foo $foo */
    return null;
}
