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
 * @since         CakePHP CodeSniffer 0.1.28
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakePHP\Sniffs\Functions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures there is a space after the function keyword for closures.
 *
 */
class ClosureDeclarationSniff implements Sniff
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [T_CLOSURE];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $spaces = 0;

        if ($tokens[($stackPtr + 1)]['code'] === T_WHITESPACE) {
            $spaces = strlen($tokens[($stackPtr + 1)]['content']);
        }

        if ($spaces !== 1) {
            $error = 'Expected 1 space after closure\'s function keyword; %s found';
            $data = [$spaces];
            $phpcsFile->addError($error, $stackPtr, 'SpaceAfterFunction', $data);
        }
    }
}
