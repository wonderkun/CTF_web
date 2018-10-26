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
 * @since         CakePHP CodeSniffer 0.1.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Makes sure there are spaces between the concatenation operator (.) and
 * the strings being concatenated.
 *
 */
namespace CakePHP\Sniffs\Strings;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class ConcatenationSpacingSniff implements Sniff
{

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [T_STRING_CONCAT];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        if ($tokens[($stackPtr - 1)]['code'] !== T_WHITESPACE) {
            $message = 'Expected 1 space before ., but 0 found';
            $phpcsFile->addError($message, $stackPtr, 'MissingBefore');
        } else {
            $content = str_replace("\r\n", "\n", $tokens[($stackPtr - 1)]['content']);
            $spaces = strlen($content);
            if ($spaces > 1) {
                $message = 'Expected 1 space before ., but %d found';
                $data = [$spaces];
                $phpcsFile->addError($message, $stackPtr, 'TooManyBefore', $data);
            }
        }

        if ($tokens[($stackPtr + 1)]['code'] !== T_WHITESPACE) {
            $message = 'Expected 1 space after ., but 0 found';
            $phpcsFile->addError($message, $stackPtr, 'MissingAfter');
        } else {
            $content = str_replace("\r\n", "\n", $tokens[($stackPtr + 1)]['content']);
            $spaces = strlen($content);
            if ($spaces > 1) {
                $message = 'Expected 1 space after ., but %d found';
                $data = [$spaces];
                $phpcsFile->addError($message, $stackPtr, 'TooManyAfter', $data);
            }
        }
    }
}
