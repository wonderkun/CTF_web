<?php
/**
 * Parses and verifies the doc comments for functions.
 *
 * PHP version 5
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
namespace CakePHP\Sniffs\Commenting;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Standards\PEAR\Sniffs\Commenting\FunctionCommentSniff as PearFunctionCommentSniff;
use PHP_CodeSniffer\Util\Common;

/**
 * Parses and verifies the doc comments for functions.
 *
 * Verifies that :
 * <ul>
 *  <li>A comment exists</li>
 *  <li>There is a blank newline after the short description</li>
 *  <li>There is a blank newline between the long and short description</li>
 *  <li>There is a blank newline between the long description and tags</li>
 *  <li>Parameter names represent those in the method</li>
 *  <li>Parameter comments are in the correct order</li>
 *  <li>Parameter comments are complete</li>
 *  <li>A type hint is provided for array and custom class</li>
 *  <li>Type hint matches the actual variable/class type</li>
 *  <li>A blank line is present before the first and after the last parameter</li>
 *  <li>A return type exists</li>
 *  <li>Any throw tag must have a comment</li>
 *  <li>The tag order and indentation are correct</li>
 * </ul>
 *
 * @category  PHP
 * @package   PHP_CodeSniffer
 * @author    Greg Sherwood <gsherwood@squiz.net>
 * @author    Marc McIntyre <mmcintyre@squiz.net>
 * @copyright 2006-2014 Squiz Pty Ltd (ABN 77 084 670 600)
 * @license   https://github.com/squizlabs/PHP_CodeSniffer/blob/master/licence.txt BSD Licence
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_CodeSniffer
 */
class FunctionCommentSniff extends PearFunctionCommentSniff
{
    /**
     * Is the comment an inheritdoc?
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token in the stack passed in $tokens.
     * @return bool True if the comment is an inheritdoc
     */
    protected function isInheritDoc(File $phpcsFile, $stackPtr)
    {
        $start = $phpcsFile->findPrevious(T_DOC_COMMENT_OPEN_TAG, $stackPtr - 1);
        $end = $phpcsFile->findNext(T_DOC_COMMENT_CLOSE_TAG, $start);
        $content = $phpcsFile->getTokensAsString($start, ($end - $start));

        return preg_match('/{@inheritDoc}/i', $content) === 1;
    } // end isInheritDoc()

    /**
     * Process the return comment of this function comment.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token in the stack passed in $tokens.
     * @param int $commentStart The position in the stack where the comment started.
     * @return void
     */
    protected function processReturn(File $phpcsFile, $stackPtr, $commentStart)
    {
        if ($this->isInheritDoc($phpcsFile, $stackPtr)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        // Skip constructor and destructor.
        $className = '';
        foreach ($tokens[$stackPtr]['conditions'] as $condPtr => $condition) {
            if ($condition === T_CLASS || $condition === T_INTERFACE) {
                $className = $phpcsFile->getDeclarationName($condPtr);
                $className = strtolower(ltrim($className, '_'));
            }
        }

        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        $isSpecialMethod = ($methodName === '__construct' || $methodName === '__destruct');
        if ($methodName !== '_') {
            $methodName = strtolower(ltrim($methodName, '_'));
        }

        $return = null;
        foreach ($tokens[$commentStart]['comment_tags'] as $tag) {
            if ($tokens[$tag]['content'] === '@return') {
                if ($return !== null) {
                    $error = 'Only 1 @return tag is allowed in a function comment';
                    $phpcsFile->addError($error, $tag, 'DuplicateReturn');

                    return;
                }

                $return = $tag;
            }
        }

        if ($isSpecialMethod === true) {
            return;
        }

        if ($return === null) {
            $error = 'Missing @return tag in function comment';
            $phpcsFile->addWarning($error, $tokens[$commentStart]['comment_closer'], 'MissingReturn');

            return;
        }//end if

        $content = $tokens[($return + 2)]['content'];
        if (empty($content) === true || $tokens[($return + 2)]['code'] !== T_DOC_COMMENT_STRING) {
            $error = 'Return type missing for @return tag in function comment';
            $phpcsFile->addError($error, $return, 'MissingReturnType');

            return;
        }

        // Check return type (can be multiple, separated by '|').
        list($types, ) = explode(' ', $content);
        $typeNames = explode('|', $types);
        $suggestedNames = [];
        foreach ($typeNames as $i => $typeName) {
            if ($typeName === 'integer') {
                $suggestedName = 'int';
            } elseif ($typeName === 'boolean') {
                $suggestedName = 'bool';
            } elseif (in_array($typeName, ['int', 'bool'])) {
                $suggestedName = $typeName;
            } else {
                $suggestedName = Common::suggestType($typeName);
            }
            if (in_array($suggestedName, $suggestedNames) === false) {
                $suggestedNames[] = $suggestedName;
            }
        }

        $suggestedType = implode('|', $suggestedNames);
        if ($types !== $suggestedType) {
            $error = 'Expected "%s" but found "%s" for function return type';
            $data = [
                $suggestedType,
                $types,
            ];
            $phpcsFile->addError($error, $return, 'InvalidReturn', $data);
        }

        $endToken = isset($tokens[$stackPtr]['scope_closer']) ? $tokens[$stackPtr]['scope_closer'] : false;
        if (!$endToken) {
            return;
        }

        // If the return type is void, make sure there is
        // no non-void return statements in the function.
        if ($typeNames === ['void']) {
            for ($returnToken = $stackPtr; $returnToken < $endToken; $returnToken++) {
                if ($tokens[$returnToken]['code'] === T_CLOSURE) {
                    $returnToken = $tokens[$returnToken]['scope_closer'];
                    continue;
                }

                if ($tokens[$returnToken]['code'] === T_RETURN
                    || $tokens[$returnToken]['code'] === T_YIELD
                ) {
                    break;
                }
            }

            if ($returnToken !== $endToken) {
                // If the function is not returning anything, just
                // exiting, then there is no problem.
                $semicolon = $phpcsFile->findNext(T_WHITESPACE, ($returnToken + 1), null, true);
                if ($tokens[$semicolon]['code'] !== T_SEMICOLON) {
                    $error = 'Function return type is void, but function contains return statement';
                    $phpcsFile->addWarning($error, $return, 'InvalidReturnVoid');
                }
            }

            return;
        }

        // If return type is not void, there needs to be a return statement
        // somewhere in the function that returns something.
        if (!in_array('mixed', $typeNames, true) && !in_array('void', $typeNames, true)) {
            $returnToken = $phpcsFile->findNext([T_RETURN, T_YIELD], $stackPtr, $endToken);
            if ($returnToken === false) {
                $error = 'Function return type is not void, but function has no return statement';
                $phpcsFile->addWarning($error, $return, 'InvalidNoReturn');
            } else {
                $semicolon = $phpcsFile->findNext(T_WHITESPACE, ($returnToken + 1), null, true);
                if ($tokens[$semicolon]['code'] === T_SEMICOLON) {
                    $error = 'Function return type is not void, but function is returning void here';
                    $phpcsFile->addWarning($error, $returnToken, 'InvalidReturnNotVoid');
                }
            }
        }//end if
    }//end processReturn()


    /**
     * Process any throw tags that this function comment has.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token in the stack passed in $tokens.
     * @param int $commentStart The position in the stack where the comment started.
     * @return void
     */
    protected function processThrows(File $phpcsFile, $stackPtr, $commentStart)
    {
        $tokens = $phpcsFile->getTokens();

        foreach ($tokens[$commentStart]['comment_tags'] as $pos => $tag) {
            if ($tokens[$tag]['content'] !== '@throws') {
                continue;
            }

            $exception = $comment = null;
            if ($tokens[($tag + 2)]['code'] === T_DOC_COMMENT_STRING) {
                $matches = [];
                preg_match('/([^\s]+)(?:\s+(.*))?/', $tokens[($tag + 2)]['content'], $matches);
                $exception = $matches[1];
                if (isset($matches[2]) === true) {
                    $comment = $matches[2];
                }
            }

            if ($exception === null) {
                $error = 'Exception type and comment missing for @throws tag in function comment';
                $phpcsFile->addWarning($error, $tag, 'InvalidThrows');
            } elseif ($comment === null) {
                $error = 'Comment missing for @throws tag in function comment';
                $phpcsFile->addWarning($error, $tag, 'EmptyThrows');
            } else {
                // Any strings until the next tag belong to this comment.
                if (isset($tokens[$commentStart]['comment_tags'][($pos + 1)]) === true) {
                    $end = $tokens[$commentStart]['comment_tags'][($pos + 1)];
                } else {
                    $end = $tokens[$commentStart]['comment_closer'];
                }

                for ($i = ($tag + 3); $i < $end; $i++) {
                    if ($tokens[$i]['code'] === T_DOC_COMMENT_STRING) {
                        $comment .= ' ' . $tokens[$i]['content'];
                    }
                }

                // Starts with a capital letter and ends with a fullstop.
                $firstChar = $comment{0};
                if (strtoupper($firstChar) !== $firstChar) {
                    $error = '@throws tag comment must start with a capital letter';
                    $phpcsFile->addWarning($error, ($tag + 2), 'ThrowsNotCapital');
                }

                $lastChar = substr($comment, -1);
                if ($lastChar !== '.') {
                    $error = '@throws tag comment must end with a full stop';
                    $phpcsFile->addWarning($error, ($tag + 2), 'ThrowsNoFullStop');
                }
            }//end if
        }//end foreach
    }//end processThrows()


    /**
     * Process the function parameter comments.
     *
     * @param \PHP_CodeSniffer\Files\File $phpcsFile The file being scanned.
     * @param int $stackPtr The position of the current token in the stack passed in $tokens.
     * @param int $commentStart The position in the stack where the comment started.
     * @return void
     */
    protected function processParams(File $phpcsFile, $stackPtr, $commentStart)
    {
        if ($this->isInheritDoc($phpcsFile, $stackPtr)) {
            return;
        }

        $tokens = $phpcsFile->getTokens();

        $params = [];
        $maxType = $maxVar = 0;
        foreach ($tokens[$commentStart]['comment_tags'] as $pos => $tag) {
            if ($tokens[$tag]['content'] !== '@param') {
                continue;
            }

            $type = $var = $comment = '';
            $typeSpace = $varSpace = 0;
            $commentLines = [];
            if ($tokens[($tag + 2)]['code'] === T_DOC_COMMENT_STRING) {
                $matches = [];
                preg_match('/([^$&]+)(?:((?:\$|&)[^\s]+)(?:(\s+)(.*))?)?/', $tokens[($tag + 2)]['content'], $matches);

                $typeLen = strlen($matches[1]);
                $type = trim($matches[1]);
                $typeSpace = ($typeLen - strlen($type));
                $typeLen = strlen($type);
                if ($typeLen > $maxType) {
                    $maxType = $typeLen;
                }

                if (isset($matches[2]) === true) {
                    $var = $matches[2];
                    $varLen = strlen($var);
                    if ($varLen > $maxVar) {
                        $maxVar = $varLen;
                    }

                    if (isset($matches[4]) === true) {
                        $varSpace = strlen($matches[3]);
                        $comment = $matches[4];
                        $commentLines[] = [
                            'comment' => $comment,
                            'token' => ($tag + 2),
                            'indent' => $varSpace,
                        ];

                        // Any strings until the next tag belong to this comment.
                        if (isset($tokens[$commentStart]['comment_tags'][($pos + 1)]) === true) {
                            $end = $tokens[$commentStart]['comment_tags'][($pos + 1)];
                        } else {
                            $end = $tokens[$commentStart]['comment_closer'];
                        }

                        for ($i = ($tag + 3); $i < $end; $i++) {
                            if ($tokens[$i]['code'] === T_DOC_COMMENT_STRING) {
                                $indent = 0;
                                if ($tokens[($i - 1)]['code'] === T_DOC_COMMENT_WHITESPACE) {
                                    $indent = strlen($tokens[($i - 1)]['content']);
                                }

                                $comment .= ' ' . $tokens[$i]['content'];
                                $commentLines[] = [
                                    'comment' => $tokens[$i]['content'],
                                    'token' => $i,
                                    'indent' => $indent,
                                ];
                            }
                        }
                    } else {
                        $error = 'Missing parameter comment';
                        $phpcsFile->addError($error, $tag, 'MissingParamComment');
                        $commentLines[] = ['comment' => ''];
                    }//end if
                } else {
                    $error = 'Missing parameter name';
                    $phpcsFile->addError($error, $tag, 'MissingParamName');
                }//end if
            } else {
                $error = 'Missing parameter type';
                $phpcsFile->addError($error, $tag, 'MissingParamType');
            }//end if

            $params[] = compact('tag', 'type', 'var', 'comment', 'commentLines', 'typeSpace', 'varSpace');
        }//end foreach

        $realParams = $phpcsFile->getMethodParameters($stackPtr);
        $foundParams = [];

        foreach ($params as $pos => $param) {
            // If the type is empty, the whole line is empty.
            if ($param['type'] === '') {
                continue;
            }

            // Check the param type value.
            $typeNames = explode('|', $param['type']);
            foreach ($typeNames as $typeName) {
                if ($typeName === 'integer') {
                    $suggestedName = 'int';
                } elseif ($typeName === 'boolean') {
                    $suggestedName = 'bool';
                } elseif (in_array($typeName, ['int', 'bool'])) {
                    $suggestedName = $typeName;
                } else {
                    $suggestedName = Common::suggestType($typeName);
                }

                if ($typeName !== $suggestedName) {
                    $error = 'Expected "%s" but found "%s" for parameter type';
                    $data = [$suggestedName, $typeName];

                    $fix = $phpcsFile->addFixableError($error, $param['tag'], 'IncorrectParamVarName', $data);
                    if ($fix === true) {
                        $content = $suggestedName;
                        $content .= str_repeat(' ', $param['typeSpace']);
                        $content .= $param['var'];
                        $content .= str_repeat(' ', $param['varSpace']);
                        $content .= $param['commentLines'][0]['comment'];
                        $phpcsFile->fixer->replaceToken(($param['tag'] + 2), $content);
                    }
                }
            }//end foreach

            if ($param['var'] === '') {
                continue;
            }

            $foundParams[] = $param['var'];

            // Make sure the param name is correct.
            if (isset($realParams[$pos]) === true) {
                $realName = $realParams[$pos]['name'];
                if ($realName !== $param['var']) {
                    $code = 'ParamNameNoMatch';
                    $data = [$param['var'], $realName];

                    $error = 'Doc comment for parameter %s does not match ';
                    if (strtolower($param['var']) === strtolower($realName)) {
                        $error .= 'case of ';
                        $code = 'ParamNameNoCaseMatch';
                    }

                    $error .= 'actual variable name %s';

                    $fix = $phpcsFile->addFixableWarning($error, $param['tag'], $code, $data);

                    if ($fix === true) {
                        $content = $suggestedName;
                        $content .= str_repeat(' ', $param['typeSpace']);
                        $content .= $realName;
                        $content .= str_repeat(' ', $param['varSpace']);
                        $content .= $param['commentLines'][0]['comment'];
                        $phpcsFile->fixer->replaceToken(($param['tag'] + 2), $content);
                    }
                }
            } elseif (substr($param['var'], -4) !== ',...') {
                // We must have an extra parameter comment.
                $error = 'Superfluous parameter comment';
                $phpcsFile->addError($error, $param['tag'], 'ExtraParamComment');
            }//end if

            if ($param['comment'] === '') {
                continue;
            }

            // Param comments must start with a capital letter and end with the full stop.
            $firstChar = $param['comment']{0};
            if (preg_match('|\p{Lu}|u', $firstChar) === 0) {
                $error = 'Parameter comment must start with a capital letter';
                $phpcsFile->addWarning($error, $param['tag'], 'ParamCommentNotCapital');
            }

            $lastChar = substr($param['comment'], -1);
            if ($lastChar !== '.') {
                $error = 'Parameter comment must end with a full stop';
                $phpcsFile->addWarning($error, $param['tag'], 'ParamCommentFullStop');
            }
        }//end foreach

        $realNames = [];
        foreach ($realParams as $realParam) {
            $realNames[] = $realParam['name'];
        }

        // Report missing comments.
        $diff = array_diff($realNames, $foundParams);
        foreach ($diff as $neededParam) {
            $error = 'Doc comment for parameter "%s" missing';
            $data = [$neededParam];
            $phpcsFile->addWarning($error, $commentStart, 'MissingParamTag', $data);
        }
    }//end processParams()
}//end class
