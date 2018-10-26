<?php
namespace CakePHP\Sniffs\Formatting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

/**
 * Throws errors if there's no blank line before return statements.
 *
 * @author   Authors <Symfony2-coding-standard@escapestudios.github.com>
 * @license  http://spdx.org/licenses/MIT MIT License
 * @link     https://github.com/escapestudios/Symfony2-coding-standard
 */
class BlankLineBeforeReturnSniff implements Sniff
{
    /**
     * A list of tokenizers this sniff supports.
     *
     * @var array
     */
    public $supportedTokenizers = [
        'PHP',
        'JS',
    ];

    /**
     * {@inheritDoc}
     */
    public function register()
    {
        return [T_RETURN];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $current = $stackPtr;
        $previousLine = $tokens[$stackPtr]['line'] - 1;
        $prevLineTokens = [];

        while ($current >= 0 && $tokens[$current]['line'] >= $previousLine) {
            if ($tokens[$current]['line'] == $previousLine
                && $tokens[$current]['type'] !== 'T_WHITESPACE'
                && $tokens[$current]['type'] !== 'T_COMMENT'
                && $tokens[$current]['type'] !== 'T_DOC_COMMENT_OPEN_TAG'
                && $tokens[$current]['type'] !== 'T_DOC_COMMENT_TAG'
                && $tokens[$current]['type'] !== 'T_DOC_COMMENT_STRING'
                && $tokens[$current]['type'] !== 'T_DOC_COMMENT_CLOSE_TAG'
                && $tokens[$current]['type'] !== 'T_DOC_COMMENT_WHITESPACE'
            ) {
                $prevLineTokens[] = $tokens[$current]['type'];
            }
            $current--;
        }

        if (isset($prevLineTokens[0])
            && ($prevLineTokens[0] === 'T_OPEN_CURLY_BRACKET'
                || $prevLineTokens[0] === 'T_COLON'
                || $prevLineTokens[0] === 'T_OPEN_TAG')
        ) {
            return;
        } elseif (count($prevLineTokens) > 0) {
            $fix = $phpcsFile->addFixableError(
                'Missing blank line before return statement',
                $stackPtr,
                'BlankLineBeforeReturn'
            );
            if ($fix === true) {
                $phpcsFile->fixer->beginChangeset();
                $phpcsFile->fixer->addNewlineBefore($stackPtr - 1);
                $phpcsFile->fixer->endChangeset();
            }
        }
    }
}
