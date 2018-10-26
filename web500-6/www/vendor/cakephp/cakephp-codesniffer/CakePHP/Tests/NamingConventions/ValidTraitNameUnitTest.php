<?php

namespace CakePHP\Tests\NamingConventions;

use PHP_CodeSniffer\Tests\Standards\AbstractSniffUnitTest;

class ValidTraitNameUnitTest extends AbstractSniffUnitTest
{
    /**
     * {@inheritDoc}
     */
    public function getErrorList()
    {
        return [
            2 => 1,
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
