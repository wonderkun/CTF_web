<?php

namespace Aptoma\Twig\Extension;

use Aptoma\Twig\TokenParser\MarkdownTokenParser;

/**
 * MarkdownExtension provides support for Markdown.
 *
 * @author Gunnar Lium <gunnar@aptoma.com>
 * @author Joris Berthelot <joris@berthelot.tel>
 */
class MarkdownExtension extends \Twig_Extension
{

    /**
     * @var MarkdownEngineInterface $markdownEngine
     */
    private $markdownEngine;

    /**
     * @param MarkdownEngineInterface $markdownEngine The Markdown parser engine
     */
    public function __construct(MarkdownEngineInterface $markdownEngine)
    {
        $this->markdownEngine = $markdownEngine;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        return array(
            new \Twig_SimpleFilter(
                'markdown',
                array($this, 'parseMarkdown'),
                array('is_safe' => array('html'))
            )
        );
    }

    /**
     * Transform Markdown content to HTML
     *
     * @param $content The Markdown content to be transformed
     * @return string The result of the Markdown engine transformation
     */
    public function parseMarkdown($content)
    {
        return $this->markdownEngine->transform($content);
    }

    /**
     * {@inheritdoc}
     */
    public function getTokenParsers()
    {
        return array(new MarkdownTokenParser($this->markdownEngine));
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'markdown';
    }
}
