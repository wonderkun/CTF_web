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
 * Ensures method names are correct depending on whether they are public
 * or private, and that functions are named correctly.
 *
 */
namespace CakePHP\Sniffs\NamingConventions;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\AbstractScopeSniff;

class ValidFunctionNameSniff extends AbstractScopeSniff
{

    /**
     * A list of all PHP magic methods.
     *
     * @var array
     */
    protected $_magicMethods = [
        'construct',
        'destruct',
        'call',
        'callStatic',
        'debugInfo',
        'get',
        'set',
        'isset',
        'unset',
        'sleep',
        'wakeup',
        'toString',
        'set_state',
        'clone',
        'invoke',
    ];

    /**
     * {@inheritDoc}
     */
    public function __construct()
    {
        parent::__construct([T_CLASS, T_INTERFACE, T_TRAIT], [T_FUNCTION], true);
    }

    /**
     * {@inheritDoc}
     */
    protected function processTokenWithinScope(File $phpcsFile, $stackPtr, $currScope)
    {
        $methodName = $phpcsFile->getDeclarationName($stackPtr);
        $className = $phpcsFile->getDeclarationName($currScope);
        $errorData = [$className . '::' . $methodName];

        // Ignore magic methods
        if (preg_match('/^__(' . implode('|', $this->_magicMethods) . ')$/', $methodName)) {
            return;
        }

        $methodProps = $phpcsFile->getMethodProperties($stackPtr);
        if ($methodProps['scope_specified'] === false) {
            // Let another sniffer take care of that
            return;
        }

        $isPublic = $methodProps['scope'] === 'public';

        if ($isPublic === true && $methodName[0] === '_') {
            $error = 'Public method name "%s" must not be prefixed with underscore';
            $phpcsFile->addError($error, $stackPtr, 'PublicWithUnderscore', $errorData);

            return;
        }
    }

    /**
     * {@inheritDoc}
     */
    protected function processTokenOutsideScope(File $phpcsFile, $stackPtr)
    {
    }
}
