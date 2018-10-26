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
 * @since         1.2.2
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Shell\Task;

/**
 * Shell Task code generator.
 *
 * @property \Bake\Shell\Task\BakeTemplateTask $BakeTemplate
 * @property \Bake\Shell\Task\TestTask $Test
 */
class TaskTask extends SimpleBakeTask
{
    /**
     * Task name used in path generation.
     *
     * @var string
     */
    public $pathFragment = 'Shell/Task/';

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return 'task';
    }

    /**
     * {@inheritDoc}
     */
    public function fileName($name)
    {
        return $name . 'Task.php';
    }

    /**
     * {@inheritDoc}
     */
    public function template()
    {
        return 'Shell/task';
    }
}
