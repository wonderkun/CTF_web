<?php
/**
 * PHP Version 5
 *
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 * @since         CakePHP CodeSniffer 2.4.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Ensures that not more than one blank line occurs
 *
 * @author Mark Scherer
 * @license http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace CakePHP\Sniffs\WhiteSpace;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class EmptyLinesSniff implements Sniff
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [T_WHITESPACE];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[$stackPtr]['content'] === $phpcsFile->eolChar
            && isset($tokens[($stackPtr + 1)]) === true
            && $tokens[($stackPtr + 1)]['content'] === $phpcsFile->eolChar
            && isset($tokens[($stackPtr + 2)]) === true
            && $tokens[($stackPtr + 2)]['content'] === $phpcsFile->eolChar
        ) {
            $error = 'Found more than a single empty line between content';
            $fix = $phpcsFile->addFixableError($error, ($stackPtr + 3), 'EmptyLines');
            if ($fix) {
                $phpcsFile->fixer->replaceToken($stackPtr + 2, '');
            }
        }
    }
}
