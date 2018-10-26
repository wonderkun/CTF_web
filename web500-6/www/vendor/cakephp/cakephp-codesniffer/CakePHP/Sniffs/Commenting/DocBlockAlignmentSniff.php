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
 * @since         CakePHP CodeSniffer 1.0.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */

namespace CakePHP\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Ensures doc block alignments.
 */
class DocBlockAlignmentSniff implements Sniff
{
    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [T_DOC_COMMENT_OPEN_TAG];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $commentClose = $phpcsFile->findNext(T_DOC_COMMENT_CLOSE_TAG, $stackPtr);
        $afterComment = $phpcsFile->findNext(T_WHITESPACE, $commentClose + 1, null, true);
        $commentIndentation = $tokens[$stackPtr]['column'] - 1;
        $codeIndentation = $tokens[$afterComment]['column'] - 1;

        // Check for doc block opening being misaligned
        if ($commentIndentation != $codeIndentation) {
            $msg = 'Doc block not aligned with code; expected indentation of %s but found %s';
            $data = [$codeIndentation, $commentIndentation];
            $fix = $phpcsFile->addFixableError($msg, $stackPtr, 'DocBlockMisaligned', $data);
            if ($fix === true) {
                // Collect tokens to change indentation of
                $tokensToIndent = [
                    $stackPtr => $codeIndentation
                ];
                $commentOpenLine = $tokens[$stackPtr]['line'];
                $commentCloseLine = $tokens[$commentClose]['line'];
                $lineBreaksInComment = $commentCloseLine - $commentOpenLine;
                if ($lineBreaksInComment !== 0) {
                    $searchToken = $stackPtr + 1;
                    do {
                        $commentBorder = $phpcsFile->findNext(
                            [T_DOC_COMMENT_STAR, T_DOC_COMMENT_CLOSE_TAG],
                            $searchToken,
                            $commentClose + 1
                        );
                        if ($commentBorder !== false) {
                            $tokensToIndent[$commentBorder] = $codeIndentation + 1;
                            $searchToken = $commentBorder + 1;
                        }
                    } while ($commentBorder !== false);
                }

                // Update indentation
                $phpcsFile->fixer->beginChangeset();
                foreach ($tokensToIndent as $searchToken => $indent) {
                    $indentString = str_repeat(' ', $indent);
                    $isOpenTag = $tokens[$searchToken]['type'] === 'T_DOC_COMMENT_OPEN_TAG';
                    if ($isOpenTag && $commentIndentation === 0) {
                        $phpcsFile->fixer->addContentBefore($searchToken, $indentString);
                    } else {
                        $phpcsFile->fixer->replaceToken($searchToken - 1, $indentString);
                    }
                }
                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}
