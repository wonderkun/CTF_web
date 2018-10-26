<?php

namespace Aptoma\Twig\Extension\MarkdownEngine;

use Aptoma\Twig\Extension\MarkdownEngineInterface;
use Michelf\MarkdownExtra;

/**
 * MichelfMarkdownEngine.php
 *
 * Maps Michelf\MarkdownExtra to Aptoma\Twig Markdown Extension
 *
 * @author Joris Berthelot <joris@berthelot.tel>
 */
class MichelfMarkdownEngine implements MarkdownEngineInterface
{
    /**
     * {@inheritdoc}
     */
    public function transform($content)
    {
        return MarkdownExtra::defaultTransform($content);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'Michelf\Markdown';
    }
}
