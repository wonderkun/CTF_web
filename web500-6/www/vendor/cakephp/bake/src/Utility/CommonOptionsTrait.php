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
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         1.4.3
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Utility;

use Cake\Core\Configure;
use Cake\Core\Plugin;

trait CommonOptionsTrait
{

    /**
     * Set common options used by all bake tasks.
     *
     * @param \Cake\Console\ConsoleOptionParser $parser Options parser.
     * @return \Cake\Console\ConsoleOptionParser
     */
    protected function _setCommonOptions($parser)
    {
        $bakeThemes = [];
        foreach (Plugin::loaded() as $plugin) {
            $path = Plugin::classPath($plugin);
            if (is_dir($path . 'Template' . DS . 'Bake')) {
                $bakeThemes[] = $plugin;
            }
        }

        $parser->addOption('plugin', [
            'short' => 'p',
            'help' => 'Plugin to bake into.'
        ])->addOption('force', [
            'short' => 'f',
            'boolean' => true,
            'help' => 'Force overwriting existing files without prompting.'
        ])->addOption('connection', [
            'short' => 'c',
            'default' => 'default',
            'help' => 'The datasource connection to get data from.'
        ])->addOption('theme', [
            'short' => 't',
            'help' => 'The theme to use when baking code.',
            'default' => Configure::read('Bake.theme'),
            'choices' => $bakeThemes
        ]);

        return $parser;
    }
}
