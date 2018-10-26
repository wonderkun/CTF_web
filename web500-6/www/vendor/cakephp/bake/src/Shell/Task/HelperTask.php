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
 * @since         0.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Shell\Task;

/**
 * Helper code generator.
 *
 * @property \Bake\Shell\Task\BakeTemplateTask $BakeTemplate
 * @property \Bake\Shell\Task\TestTask $Test
 */
class HelperTask extends SimpleBakeTask
{
    /**
     * Task name used in path generation.
     *
     * @var string
     */
    public $pathFragment = 'View/Helper/';

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return 'helper';
    }

    /**
     * {@inheritDoc}
     */
    public function fileName($name)
    {
        return $name . 'Helper.php';
    }

    /**
     * {@inheritDoc}
     */
    public function template()
    {
        return 'View/helper';
    }
}
