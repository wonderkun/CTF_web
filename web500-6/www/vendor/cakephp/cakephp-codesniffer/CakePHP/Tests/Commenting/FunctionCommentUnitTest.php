<?php

namespace CakePHP\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class FunctionCommentUnitTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritDoc}
     */
    public function getErrorList()
    {
        return [
            12 => 1,
            13 => 1,
            23 => 1,
            24 => 1,
            34 => 1,
            35 => 1,
            90 => 1,
            97 => 1,
            104 => 1,
            112 => 1,
            222 => 1,
            231 => 1,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getWarningList()
    {
        return [
            14 => 1,
            31 => 2,
            45 => 1,
            140 => 1,
            145 => 1,
            155 => 1,
            165 => 1,
            174 => 1,
            182 => 1,
            190 => 1,
            197 => 1,
            198 => 1,
            205 => 1,
            206 => 1,
            215 => 1,
            221 => 1,
        ];
    }
}
