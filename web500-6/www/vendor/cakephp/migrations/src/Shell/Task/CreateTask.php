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

use Phinx\Console\Command\Create;

/**
 * This task class is needed in order to provide a correct autocompletion feature
 * when using the CakePHP migrations shell plugin. It has no effect on the
 * migrations process.
 */
class CreateTask extends CommandTask
{

    /**
     * {@inheritDoc}
     */
    public function getOptionParser()
    {
        $parser = parent::getOptionParser();
        $parser
            ->addArgument('name', [
                'help' => 'What is the name of the migration?'
            ])
            ->addOption('template', [
                'short' => 't',
                'help' => 'Use an alternative template',
                'required' => false
            ])
            ->addOption('class', [
                'short' => 'l',
                'help' => 'Use a class implementing "' . Create::CREATION_INTERFACE . '" to generate the template',
                'required' => false
            ]);

        return $parser;
    }
}
