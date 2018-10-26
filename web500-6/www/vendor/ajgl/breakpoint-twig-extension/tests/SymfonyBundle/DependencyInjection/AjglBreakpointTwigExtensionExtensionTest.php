<?php

/*
 * AJGL Breakpoint Twig Extension Component
 *
 * Copyright (C) Antonio J. García Lagar <aj@garcialagar.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Ajgl\Twig\Extension\Tests\SymfonyBundle\DependencyInjection;

use Ajgl\Twig\Extension\SymfonyBundle\DependencyInjection\AjglBreakpointTwigExtensionExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Antonio J. García Lagar <aj@garcialagar.es>
 */
class AjglBreakpointTwigExtensionExtensionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ContainerBuilder
     */
    protected $container;

    /**
     * @var AjglBreakpointTwigExtensionExtension
     */
    protected $extension;

    protected function setUp()
    {
        $this->container = new ContainerBuilder();
        $this->extension = new AjglBreakpointTwigExtensionExtension();
    }

    public function testTwigExtensionsDefinition()
    {
        $this->extension->load(array(), $this->container);
        $this->assertTrue($this->container->hasDefinition('ajgl_twig_extension.breakpoint'));
        $definition = $this->container->getDefinition('ajgl_twig_extension.breakpoint');
        $this->assertSame(
            'Ajgl\Twig\Extension\BreakpointExtension',
            $definition->getClass()
        );
        $this->assertNotNull($definition->getTag('twig.extension'));
    }
}
