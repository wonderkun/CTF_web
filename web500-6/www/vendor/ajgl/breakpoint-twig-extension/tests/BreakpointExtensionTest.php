<?php

/*
 * AJGL Breakpoint Twig Extension Component
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Twig\Extension\Tests;

use Ajgl\Twig\Extension\BreakpointExtension;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class BreakpointExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var BreakpointExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->extension = new BreakpointExtension();
    }

    public function testGetName()
    {
        $this->assertSame('breakpoint', $this->extension->getName());
    }

    public function testGetFunctions()
    {
        $functions = $this->extension->getFunctions();
        $this->assertCount(1, $functions);
        $function = reset($functions);
        $this->assertInstanceOf('Twig_SimpleFunction', $function);
        $callable = $function->getCallable();
        $this->assertTrue(is_array($callable));
        $this->assertCount(2, $callable);
        $this->assertSame($this->extension, $callable[0]);
        $this->assertSame('setBreakpoint', $callable[1]);
    }
}
