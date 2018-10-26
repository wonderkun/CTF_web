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
 * @since         1.1.0
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace Bake\Shell\Task;

use Cake\Utility\Inflector;

/**
 * Mailer code generator.
 *
 * @property \Bake\Shell\Task\BakeTemplateTask $BakeTemplate
 * @property \Bake\Shell\Task\TestTask $Test
 */
class MailerTask extends SimpleBakeTask
{
    /**
     * Task name used in path generation.
     *
     * @var string
     */
    public $pathFragment = 'Mailer/';

    /**
     * {@inheritDoc}
     */
    public function name()
    {
        return 'mailer';
    }

    /**
     * {@inheritDoc}
     */
    public function fileName($name)
    {
        return $name . 'Mailer.php';
    }

    /**
     * {@inheritDoc}
     */
    public function template()
    {
        return 'Mailer/mailer';
    }

    /**
     * Bake the Mailer class and html/text layout files.
     *
     * @param string $name The name of the mailer to make.
     * @return string
     */
    public function bake($name)
    {
        $this->bakeLayouts($name);

        return parent::bake($name);
    }

    /**
     * Bake empty layout files for html/text emails.
     *
     * @param string $name The name of the mailer layouts are needed for.
     * @return void
     */
    public function bakeLayouts($name)
    {
        $restore = $this->pathFragment;
        $layoutsPath = implode(DS, ['Template', 'Layout', 'Email']);

        foreach (['html', 'text'] as $type) {
            $this->pathFragment = implode(DS, [$layoutsPath, $type, Inflector::underscore($name) . '.ctp']);
            $path = $this->getPath();
            $this->createFile($path, '');
        }

        $this->pathFragment = $restore;
    }
}
