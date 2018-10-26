<?php

namespace Aptoma\Twig\Extension\MarkdownEngine;

use Aptoma\Twig\Extension\MarkdownEngineInterface;
use League\CommonMark\CommonMarkConverter;

/**
 * PHPLeagueCommonMarkEngine.php
 *
 * Maps League\CommonMark\CommonMarkConverter to Aptoma\Twig Markdown Extension
 *
 * @author Casey McLaughlin <caseyamcl@gmail.com>
 */
class PHPLeagueCommonMarkEngine implements MarkdownEngineInterface
{
    /**
     * @var \League\CommonMark\CommonMarkConverter
     */
    private $converter;

    /**
     * Constructor
     *
     * Accepts CommonMarkConverter or creates one automatically
     *
     * @param \League\CommonMark\CommonMarkConverter $converter
     */
    public function __construct(CommonMarkConverter $converter = null)
    {
        $this->converter = $converter ?: new CommonMarkConverter();
    }

    /**
     * {@inheritdoc}
     */
    public function transform($content)
    {
        return $this->converter->convertToHtml($content);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'League\CommonMark';
    }
}
