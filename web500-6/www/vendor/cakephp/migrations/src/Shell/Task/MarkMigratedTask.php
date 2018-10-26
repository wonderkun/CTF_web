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

/**
 * This task class is needed in order to provide a correct autocompletion feature
 * when using the CakePHP migrations shell plugin. It has no effect on the
 * migrations process.
 */
class MarkMigratedTask extends CommandTask
{

    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser
            ->addArgument('version', [
                'help' => 'What is the version of the migration?'
            ])
            ->addOption('exclude', [
                'short' => 'x',
                'help' => 'If present it will mark migrations from beginning until the given version, excluding it',
                'required' => false
            ])
            ->addOption('only', [
                'short' => 'o',
                'help' => 'If present it will only mark the given migration version',
                'required' => false
            ]);

        return $parser;
    }
}
