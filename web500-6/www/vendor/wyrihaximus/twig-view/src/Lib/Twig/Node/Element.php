<?php

/**
 * This file is part of TwigView.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\TwigView\Lib\Twig\Node;

/**
 * Class Element
 * @package WyriHaximus\TwigView\Lib\Twig\Node
 */
class Element extends \Twig_Node
{
    /**
     * Constructor.
     *
     * @param \Twig_Node_Expression $name    Name.
     * @param \Twig_Node_Expression $data    Data.
     * @param \Twig_Node_Expression $options Options.
     * @param string                $lineno  Linenumber.
     * @param string                $tag     Tag.
     */
    public function __construct(
        \Twig_Node_Expression $name,
        \Twig_Node_Expression $data = null,
        \Twig_Node_Expression $options = null,
        $lineno = '',
        $tag = null
    ) {
        if ($data === null) {
            $data = new \Twig_Node_Expression_Array([], $lineno);
        }

        if ($options === null) {
            $options = new \Twig_Node_Expression_Array([], $lineno);
        }

        parent::__construct(
            [
                'name' => $name,
                'data' => $data,
                'options' => $options,
            ],
            [],
            $lineno,
            $tag
        );
    }

    /**
     * Compile node.
     *
     * @param \Twig_Compiler $compiler Compiler.
     *
     * @return void
     */
    public function compile(\Twig_Compiler $compiler)
    {
        $compiler->addDebugInfo($this);

        $compiler->raw('echo $context[\'_view\']->element(');
        $compiler->subcompile($this->getNode('name'));
        $data = $this->getNode('data');
        if ($data !== null) {
            $compiler->raw(',');
            $compiler->subcompile($data);
        }
        $options = $this->getNode('options');
        if ($options !== null) {
            $compiler->raw(',');
            $compiler->subcompile($options);
        }
        $compiler->raw(");\n");
    }
}
