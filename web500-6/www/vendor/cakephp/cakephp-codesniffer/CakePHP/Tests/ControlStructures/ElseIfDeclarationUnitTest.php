<?php

namespace CakePHP\Tests\ControlStructures;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class ElseIfDeclarationUnitTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritDoc}
     */
    public function getErrorList()
    {
        return [
            4 => 1,
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
