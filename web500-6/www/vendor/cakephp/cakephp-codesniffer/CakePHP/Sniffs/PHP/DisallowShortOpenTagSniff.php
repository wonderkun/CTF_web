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
 * Disallow short open tags
 *
 * But permit short-open echo tags (<?=) [T_OPEN_TAG_WITH_ECHO] as they are part of PHP 5.4+
 *
 */
namespace CakePHP\Sniffs\PHP;

use PHP_CodeSniffer\Files\File;
use PHP_CodeSniffer\Sniffs\Sniff;

class DisallowShortOpenTagSniff implements Sniff
{

    /**
     * Returns an array of tokens this test wants to listen for.
     *
     * If short open tags are NOT enabled, <? is not considered a T_OPEN_TAG
     * So include T_INLINE_HTML which is what "<?" is detected as
     *
     * @return array
     */
    public function register()
    {
        return [
            T_OPEN_TAG,
            T_INLINE_HTML,
        ];
    }

    /**
     * {@inheritDoc}
     */
    public function process(File $phpcsFile, $stackPtr)
    {
        $tokens = $phpcsFile->getTokens();
        $openTag = $tokens[$stackPtr];

        if (trim($openTag['content']) === '<?') {
            $error = 'Short PHP opening tag used; expected "<?php" but found "%s"';
            $data = [trim($openTag['content'])];
            $phpcsFile->addError($error, $stackPtr, 'Found', $data);
        }
    }
}
