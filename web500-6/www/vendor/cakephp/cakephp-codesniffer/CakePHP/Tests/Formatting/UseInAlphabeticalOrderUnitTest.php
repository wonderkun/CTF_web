<?php

namespace CakePHP\Tests\Formatting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class UseInAlphabeticalOrderUnitTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritDoc}
     */
    public function getErrorList($testFile = '')
    {
        switch ($testFile) {
            case 'UseInAlphabeticalOrderUnitTest.1.inc':
                return [
                    3 => 1,
                    4 => 1,
                    8 => 1,
                    9 => 1,
                ];

            default:
                return [];
        }
    }

    /**
     * {@inheritDoc}
     */
    public function getWarningList()
    {
        return [];
    }
}
