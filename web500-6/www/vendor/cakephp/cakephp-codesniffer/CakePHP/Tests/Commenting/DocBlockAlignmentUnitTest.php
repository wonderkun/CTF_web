<?php

namespace CakePHP\Tests\Commenting;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class DocBlockAlignmentUnitTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritDoc}
     */
    public function getErrorList($testFile = '')
    {
        switch ($testFile) {
            case 'DocBlockAlignmentUnitTest.1.inc':
                return [
                    2 => 1,
                    7 => 1,
                    14 => 1,
                    21 => 1,
                    30 => 1
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
