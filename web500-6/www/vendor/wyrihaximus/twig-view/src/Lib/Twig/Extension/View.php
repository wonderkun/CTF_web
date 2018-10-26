<?php

/**
 * This file is part of TwigView.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\TwigView\Lib\Twig\Extension;

use Cake\View\View as CakeView;

/**
 * Class View
 * @package WyriHaximus\TwigView\Lib\Twig\Extension
 */
// @codingStandardsIgnoreStart
class View extends \Twig_Extension
// @codingStandardsIgnoreEnd
{
    /**
     * View to call methods upon.
     *
     * @var CakeView
     */
    protected $view;

    /**
     * Constructor.
     *
     * @param CakeView $view View instance.
     */
    public function __construct(CakeView $view)
    {
        $this->view = $view;
    }

    /**
     * Get declared functions.
     *
     * @return \Twig_SimpleFunction[]
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('elementExists', function ($name) {
                return $this->view->elementExists($name);
            }),
            new \Twig_SimpleFunction('getVars', function () {
                return $this->view->getVars();
            }),
            new \Twig_SimpleFunction('get', function ($var, $default = null) {
                return $this->view->get($var, $default);
            }),
        ];
    }

    /**
     * Get extension name.
     *
     * @return string
     */
    public function getName()
    {
        return 'view';
    }
}
