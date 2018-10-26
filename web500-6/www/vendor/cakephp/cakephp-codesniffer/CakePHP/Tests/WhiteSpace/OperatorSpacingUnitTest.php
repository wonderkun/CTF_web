<?php

namespace CakePHP\Tests\WhiteSpace;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class OperatorSpacingUnitTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritDoc}
     */
    public function getErrorList()
    {
        return [
            3 => 1,
            4 => 1,
            5 => 2,
            6 => 1,
            7 => 1,
            8 => 2,
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
