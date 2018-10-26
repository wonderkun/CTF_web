<?php
/**
 * PHP Version 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * This file is originally written by Greg Sherwood and Marc McIntyre, but
 * modified for CakePHP.
 *
 * @copyright     2006 Squiz Pty Ltd (ABN 77 084 670 600)
 * @link          http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 * @since         CakePHP CodeSniffer 0.1.1
 * @license       https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 */

/**
 * Ensure there is no whitespace after an object operator.
 *
 */
namespace CakePHP\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class ObjectOperatorSpacingSniff implements Sniff
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [T_OBJECT_OPERATOR];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $nextType = $tokens[($stackPtr + 1)]['code'];
        if (in_array($nextType, Tokens::$emptyTokens) === true) {
            $error = 'Space found after object operator';
            $phpcsFile->addError($error, $stackPtr, 'After');
        }
    }
}
