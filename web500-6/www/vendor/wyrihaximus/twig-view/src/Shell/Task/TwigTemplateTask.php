<?php

/**
 * This file is part of TwigView.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\TwigView\Shell\Task;

use Bake\Shell\Task\TemplateTask;
use Cake\Console\Shell;
use Cake\Core\Configure;
use Cake\Utility\Inflector;

/**
 * Task class for creating and updating twig view template files.
 *
 */
class TwigTemplateTask extends TemplateTask
{

    public function name()
    {
        return 'twig_template';
    }

    /**
     * Assembles and writes bakes the twig view file.
     *
     * @param string $template Template to generate content with.
     * @param string $content Content to write.
     * @param string $outputFile The destination action name. If null, will fallback to $template.
     * @return string Generated file content.
     */
    public function bake($template, $content = '', $outputFile = null)
    {
        if ($outputFile === null) {
            $outputFile = $template;
        }
        if ($content === true) {
            $content = $this->getContent($template);
        }
        if (empty($content)) {
            $this->err("<warning>No generated content for '{$template}.ctp', not generating template.</warning>");

            return false;
        }
        $this->out("\n" . sprintf('Baking `%s` view twig template file...', $outputFile), 1, Shell::QUIET);
        $path = $this->getPath();
        $filename = $path . Inflector::underscore($outputFile) . '.twig';
        $this->createFile($filename, $content);

        return $content;
    }
}
