<?php
/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://pear.php.net/package/PHP_CodeSniffer_CakePHP
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

/**
 * Asserts that type casts are in the short form:
 * - bool instead of boolean
 * - int instead of integer
 */
namespace CakePHP\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;
use PHP_CodeSniffer\Util\Tokens;

class TypeCastingSniff implements Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * Note, that this sniff only checks the value and casing of a cast.
     * It does not check for whitespace issues regarding casts, as
     * - Squiz.WhiteSpace.CastSpacing.ContainsWhiteSpace checks for whitespace in the cast
     * - Generic.Formatting.NoSpaceAfterCast.SpaceFound checks for whitespace after the cast
     *
     * @return array
     */
    public function register()
    {
        return array_merge(Tokens::$castTokens, [T_BOOLEAN_NOT]);
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();

        // Process !! casts
        if ($tokens[$stackPtr]['code'] == T_BOOLEAN_NOT) {
            $nextToken = $phpcsFile->findNext(T_WHITESPACE, ($stackPtr + 1), null, true);
            if (!$nextToken) {
                return;
            }
            if ($tokens[$nextToken]['code'] != T_BOOLEAN_NOT) {
                return;
            }
            $error = 'Usage of !! cast is not allowed. Please use (bool) to cast.';
            $phpcsFile->addError($error, $stackPtr, 'NotAllowed');

            return;
        }

        // Only allow short forms if both short and long forms are possible
        $matching = [
            '(boolean)' => '(bool)',
            '(integer)' => '(int)',
        ];
        $content = $tokens[$stackPtr]['content'];
        $key = strtolower($content);
        if (isset($matching[$key])) {
            $error = 'Please use ' . $matching[$key] . ' instead of ' . $content . '.';
            $phpcsFile->addError($error, $stackPtr, 'NotAllowed');

            return;
        }
        if ($content !== $key) {
            $error = 'Please use ' . $key . ' instead of ' . $content . '.';
            $phpcsFile->addError($error, $stackPtr, 'NotAllowed');

            return;
        }
    }
}
