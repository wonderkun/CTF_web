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
 * Class Cell
 * @package WyriHaximus\TwigView\Lib\Twig\Node
 */
// @codingStandardsIgnoreStart
class Cell extends \Twig_Node implements \Twig_NodeOutputInterface
// @codingStandardsIgnoreEnd
{

    /**
     * Whether to assign the data or not.
     *
     * @var boolean
     */
    protected $assign = false;

    /**
     * Constructor.
     *
     * @param boolean               $assign   Assign or echo.
     * @param mixed                 $variable Variable to assign to.
     * @param \Twig_Node_Expression $name     Name.
     * @param \Twig_Node_Expression $data     Data array.
     * @param \Twig_Node_Expression $options  Options array.
     * @param string                $lineno   Line number.
     * @param string                $tag      Tag name.
     */
    public function __construct(
        $assign,
        $variable,
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
            [
                'variable' => $variable,
            ],
            $lineno,
            $tag
        );

        $this->assign = $assign;
    }

    /**
     * Compile tag.
     *
     * @param \Twig_Compiler $compiler Compiler.
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    public function compile(\Twig_Compiler $compiler)
    {
        // @codingStandardsIgnoreEnd
        $compiler->addDebugInfo($this);

        if ($this->assign) {
            $compiler->raw('$context[\'' . $this->getAttribute('variable') . '\'] = ');
            // @codingStandardsIgnoreStart
        } else {
            // @codingStandardsIgnoreEnd
            $compiler->raw('echo ');
        }
        $compiler->raw('$context[\'_view\']->cell(');
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
