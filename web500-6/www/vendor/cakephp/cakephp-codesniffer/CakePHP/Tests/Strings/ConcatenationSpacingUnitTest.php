<?php

namespace CakePHP\Tests\Strings;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class ConcatenationSpacingUnitTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritDoc}
     */
    public function getErrorList()
    {
        return [
            4 => 1,
            5 => 1,
            6 => 2,
            7 => 1,
            8 => 1,
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
