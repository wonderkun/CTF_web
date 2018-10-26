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
 * @since         CakePHP CodeSniffer 0.1.10
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace CakePHP\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures all the use are in alphabetical order.
 *
 */
class UseInAlphabeticalOrderSniff implements Sniff
{

    /**
     * Processed files
     *
     * @var array
     */
    protected $_processed = [];

    /**
     * The list of use statements, their content and scope.
     *
     * @var array
     */
    protected $_uses = [];

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [T_USE];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        if (isset($this->_processed[$phpcsFile->getFilename()])) {
            return;
        }
        $filename = $phpcsFile->getFilename();

        $this->_uses = [];
        $next = $stackPtr;

        while ($next !== false) {
            $this->_checkUseToken($phpcsFile, $next);
            $next = $phpcsFile->findNext(T_USE, $next + 1);
        }

        // Prevent multiple uses in the same file from entering
        $this->_processed[$phpcsFile->getFilename()] = true;

        foreach ($this->_uses as $scope => $used) {
            $defined = $sorted = array_keys($used);

            natcasesort($sorted);
            $sorted = array_values($sorted);
            if ($sorted === $defined) {
                continue;
            }

            foreach ($defined as $i => $name) {
                if ($name !== $sorted[$i]) {
                    $error = 'Use classes must be in alphabetical order. Was expecting ' . $sorted[$i];
                    $phpcsFile->addError($error, $used[$name], 'UseInAlphabeticalOrder');
                }
            }
        }
    }

    /**
     * Check all the use tokens in a file.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file to check.
     * @param int $stackPtr The index of the first use token.
     * @return void
     */
    protected function _checkUseToken($phpcsFile, $stackPtr)
    {
        // If the use token is for a closure we want to ignore it.
        $isClosure = $this->_isClosure($phpcsFile, $stackPtr);
        if ($isClosure) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        $content = '';
        $end = $phpcsFile->findNext([T_SEMICOLON, T_OPEN_CURLY_BRACKET], $stackPtr);
        $useTokens = array_slice($tokens, $stackPtr, $end - $stackPtr, true);

        foreach ($useTokens as $index => $token) {
            if ($token['code'] === T_STRING || $token['code'] === T_NS_SEPARATOR) {
                $content .= $token['content'];
            }
        }

        // Check for class scoping on use. Traits should be
        // ordered independently.
        $scope = 0;
        if (!empty($token['conditions'])) {
            $scope = key($token['conditions']);
        }
        $this->_uses[$scope][$content] = $stackPtr;
    }

    /**
     * Check if the current stackPtr is a use token that is for a closure.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $stackPtr The index of the first use token.
     * @return bool
     */
    protected function _isClosure($phpcsFile, $stackPtr)
    {
        return $phpcsFile->findPrevious(
            [T_CLOSURE],
            ($stackPtr - 1),
            null,
            false,
            null,
            true
        );
    }
}
