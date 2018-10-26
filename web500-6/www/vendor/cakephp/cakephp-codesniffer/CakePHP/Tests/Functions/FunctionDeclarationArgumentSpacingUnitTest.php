<?php

namespace CakePHP\Tests\Functions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class FunctionDeclarationArgumentSpacingUnitTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritDoc}
     */
    public function getErrorList()
    {
        return [
            3 => 1,
            4 => 2,
            5 => 2,
            6 => 2,
            7 => 2,
            8 => 2,
            9 => 9,
            10 => 6,
            11 => 6,
            12 => 4,
            13 => 4,
            14 => 2,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function getWarningList()
    {
        return [];
    }
}
