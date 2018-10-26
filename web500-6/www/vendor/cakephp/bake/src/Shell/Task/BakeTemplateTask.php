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

use Bake\View\BakeView;
use Cake\Console\Shell;
use Cake\Core\ConventionsTrait;
use Cake\Event\Event;
use Cake\Event\EventManager;
use Cake\Http\Response;
use Cake\Http\ServerRequest as Request;
use Cake\View\Exception\MissingTemplateException;
use Cake\View\ViewVarsTrait;

/**
 * Used by other tasks to generate templated output, Acts as an interface to BakeView
 */
class BakeTemplateTask extends Shell
{
    use ConventionsTrait;

    use ViewVarsTrait;

    /**
     * BakeView instance
     *
     * @var \Bake\View\BakeView
     */
    public $View;

    /**
     * Get view instance
     *
     * @return \Cake\View\View
     * @triggers Bake.initialize $view
     */
    public function getView()
    {
        if ($this->View) {
            return $this->View;
        }

        $theme = isset($this->params['theme']) ? $this->params['theme'] : '';

        $viewOptions = [
            'helpers' => [
                'Bake.Bake',
                'Bake.DocBlock'
            ],
            'theme' => $theme
        ];

        $view = new BakeView(new Request(), new Response(), null, $viewOptions);
        $event = new Event('Bake.initialize', $view);
        EventManager::instance()->dispatch($event);
        $this->View = $event->subject;

        return $this->View;
    }

    /**
     * Runs the template
     *
     * @param string $template bake template to render
     * @param array|null $vars Additional vars to set to template scope.
     * @return string contents of generated code template
     */
    public function generate($template, $vars = null)
    {
        if ($vars !== null) {
            $this->set($vars);
        }

        $this->getView()->set($this->viewVars);

        try {
            return $this->View->render($template);
        } catch (MissingTemplateException $e) {
            $this->_io->verbose(sprintf('No bake template found for "%s"', $template));

            return '';
        }
    }
}
