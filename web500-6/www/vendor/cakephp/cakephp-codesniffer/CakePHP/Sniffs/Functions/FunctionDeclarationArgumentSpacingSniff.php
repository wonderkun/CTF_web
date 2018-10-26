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
 * @since         CakePHP CodeSniffer 0.1.18
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace CakePHP\Sniffs\Functions;

use PHP_CodeSniffer\Standards\Squiz\Sniffs\Functions\FunctionDeclarationArgumentSpacingSniff as SquizFunctionDeclarationArgumentSpacingSniff;

/**
 * Ensures the spacing of function declaration arguments is correct.
 *
 */
class FunctionDeclarationArgumentSpacingSniff extends SquizFunctionDeclarationArgumentSpacingSniff
{

    /**
     * How many spaces should surround the equals signs.
     *
     * @var int
     */
    public $equalsSpacing = 1;
}
