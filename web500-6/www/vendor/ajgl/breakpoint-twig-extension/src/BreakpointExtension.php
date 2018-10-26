<?php

/*
 * AJGL Breakpoint Twig Extension Component
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Twig\Extension;

use Twig_Environment;
use Twig_Extension;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class BreakpointExtension extends Twig_Extension
{
    public function getName()
    {
        return 'breakpoint';
    }

    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('breakpoint', array($this, 'setBreakpoint'), array('needs_environment' => true, 'needs_context' => true)),
        );
    }

    /**
     * If XDebug is detected, makes the debugger break.
     *
     * @param Twig_Environment $environment the environment instance
     * @param mixed            $context     variables from the Twig template
     */
    public function setBreakpoint(Twig_Environment $environment, $context)
    {
        if (function_exists('xdebug_break')) {
            $arguments = array_slice(func_get_args(), 2);
            xdebug_break();
        }
    }
}
