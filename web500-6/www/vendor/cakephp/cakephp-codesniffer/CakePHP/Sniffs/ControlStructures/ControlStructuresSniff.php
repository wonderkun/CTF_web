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
 * @since         CakePHP CodeSniffer 0.1.14
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Ensures curly brackets are used with if, else, elseif, foreach and for.
 * while and dowhile are covered elsewhere
 *
 */

namespace CakePHP\Sniffs\ControlStructures;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ControlStructuresSniff implements Sniff
{

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [T_IF, T_ELSEIF, T_ELSE, T_FOREACH, T_FOR];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
        if ($tokens[$nextToken]['code'] === T_OPEN_PARENTHESIS) {
            $closer = $tokens[$nextToken]['parenthesis_closer'];
            $diff = $closer - $stackPtr;
            $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + $diff + 1), null, true);
        }
        if ($tokens[$nextToken]['code'] === T_IF) {
            // "else if" is not checked by this sniff, another sniff takes care of that.
            return;
        }
        if ($tokens[$nextToken]['code'] !== T_OPEN_CURLY_BRACKET && $tokens[$nextToken]['code'] !== T_COLON) {
            $error = 'Curly brackets required for if/elseif/else.';
            $phpcsFile->addError($error, $stackPtr, 'NotAllowed');
        }
    }
}
