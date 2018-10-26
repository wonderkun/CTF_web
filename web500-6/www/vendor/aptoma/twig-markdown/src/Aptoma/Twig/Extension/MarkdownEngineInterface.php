<?php

namespace Aptoma\Twig\Extension;

/**
 * MarkdownEngineInterface.php
 *
 * Provide software interface to maps various Markdown engines
 *
 * @author Joris Berthelot <joris@berthelot.tel>
 */
interface MarkdownEngineInterface
{
    /**
     * Transforms the given markdown data in HTML
     *
     * @param $content Markdown data
     * @return string
     */
    public function transform($content);

    /**
     * Return Markdown engine vendor ID
     *
     * @return string
     */
    public function getName();
}
