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

use Twig_Token;
use WyriHaximus\TwigView\Lib\Twig\Node\Cell as CellNode;

/**
 * Class Element
 * @package WyriHaximus\TwigView\Lib\Twig\TokenParser
 */
class Cell extends \Twig_TokenParser_Include
{
    /**
     * Parse token.
     *
     * @param Twig_Token $token Token.
     *
     * @return CellNode
     */
    // @codingStandardsIgnoreStart
    public function parse(Twig_Token $token)
    {
        // @codingStandardsIgnoreEnd
        $stream = $this->parser->getStream();

        // @codingStandardsIgnoreStart
        $variable = null;
        if ($stream->test(Twig_Token::NAME_TYPE)) {
            $variable = $stream->expect(Twig_Token::NAME_TYPE)->getValue();
        }
        $assign = false;
        if ($stream->test(Twig_Token::OPERATOR_TYPE)) {
            $stream->expect(Twig_Token::OPERATOR_TYPE, '=');
            $assign = true;
        }

        $name = $this->parser->getExpressionParser()->parseExpression();
        $data = null;
        if (!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
            $data = $this->parser->getExpressionParser()->parseExpression();
        }
        $options = null;
        if (!$stream->test(Twig_Token::BLOCK_END_TYPE)) {
            $options = $this->parser->getExpressionParser()->parseExpression();
        }
        // @codingStandardsIgnoreEnd

        $stream->expect(Twig_Token::BLOCK_END_TYPE);

        return new CellNode($assign, $variable, $name, $data, $options, $token->getLine(), $this->getTag());
    }

    /**
     * Tag name.
     *
     * @return string
     */
    public function getTag()
    {
        return 'cell';
    }
}
