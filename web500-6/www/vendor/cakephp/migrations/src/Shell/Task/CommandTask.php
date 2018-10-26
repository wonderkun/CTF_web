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
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Migrations\Shell\Task;

use Cake\Console\Shell;

/**
 * Base Task that Migrations subcommands Task class should extend.
 * It implements the getOptionParser method that defines the common options
 * for all subcommands.
 */
class CommandTask extends Shell
{

    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser
            ->addOption('plugin', [
                'short' => 'p',
                'help' => 'The plugin the command should be applied to',
                'required' => false
            ])
            ->addOption('connection', [
                'short' => 'c',
                'help' => 'The datasource connection to use',
                'required' => false
            ])
            ->addOption('source', [
                'short' => 's',
                'help' => 'The name of the folder where migrations are stored',
                'required' => false
            ])
            ->addOption('ansi')
            ->addOption('no-ansi')
            ->addOption('no-interaction', ['short' => 'n']);

        return $parser;
    }
}
