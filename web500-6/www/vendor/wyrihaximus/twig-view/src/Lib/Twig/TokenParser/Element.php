<?php

/**
 * This file is part of TwigView.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\TwigView\Lib\Twig\TokenParser;

use WyriHaximus\TwigView\Lib\Twig\Node\Element as ElementNode;

/**
 * Class Element
 * @package WyriHaximus\TwigView\Lib\Twig\TokenParser
 */
class Element extends \Twig_TokenParser_Include
{

    /**
     * Parse token.
     *
     * @param \Twig_Token $token Token.
     *
     * @return \Twig_NodeInterface|ElementNode
     */
    // @codingStandardsIgnoreStart
    public function parse(\Twig_Token $token)
    {
        // @codingStandardsIgnoreEnd
        // @codingStandardsIgnoreStart
        $stream = $this->parser->getStream();
        $name = $this->parser->getExpressionParser()->parseExpression();

        $data = null;
        if (!$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
            $data = $this->parser->getExpressionParser()->parseExpression();
        }

        $options = null;
        if (!$stream->test(\Twig_Token::BLOCK_END_TYPE)) {
            $options = $this->parser->getExpressionParser()->parseExpression();
        }
        // @codingStandardsIgnoreEnd

        $stream->expect(\Twig_Token::BLOCK_END_TYPE);

        return new ElementNode($name, $data, $options, $token->getLine(), $this->getTag());
    }

    /**
     * Get tag name.
     *
     * @return string
     */
    public function getTag()
    {
        return 'element';
    }
}
