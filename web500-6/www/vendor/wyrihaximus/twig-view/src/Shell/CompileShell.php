<?php

/**
 * This file is part of TwigView.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\TwigView\Shell;

use Cake\Console\ConsoleIo;
use Cake\Console\Shell;
use Cake\Core\Plugin;
use WyriHaximus\TwigView\Lib\Scanner;
use WyriHaximus\TwigView\View\TwigView;

/**
 * Class CompileTemplatesShell
 * @package WyriHaximus\TwigView\Console\Command
 */
// @codingStandardsIgnoreStart
class CompileShell extends Shell
// @codingStandardsIgnoreEnd
{

    /**
     * Instance of TwigView to be used to compile templates.
     *
     * @var TwigView
     */
    protected $twigView;

    /**
     * Constructor.
     *
     * @param ConsoleIo $consoleIo An IO instance.
     */
    public function __construct(ConsoleIo $consoleIo = null)
    {
        parent::__construct($consoleIo);

        $this->twigView = new TwigView();
    }

    /**
     * Set TwigView.
     *
     * @param TwigView $twigView TwigView instance.
     *
     * @return void
     */
    public function setTwigview(TwigView $twigView)
    {
        $this->twigView = $twigView;
    }

    /**
     * Compile all templates.
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    public function all()
    {
        $this->out('<info>Compiling all templates</info>');

        foreach(Scanner::all() as $section => $templates) {
            $this->out('<info>Compiling ' . $section . '\'s templates</info>');
            $this->walkIterator($templates);
        }
    }
    // @codingStandardsIgnoreEnd

    /**
     * Compile only this plugin.
     *
     * @param string $plugin Plugin name.
     *
     * @return void
     */
    public function plugin($plugin)
    {
        $this->out('<info>Compiling one ' . $plugin . '\'s templates</info>');
        $this->walkIterator(Scanner::plugin($plugin));
    }

    /**
     * Only compile one file.
     *
     * @param string $fileName File to compile.
     *
     * @return void
     */
    public function file($fileName)
    {
        $this->out('<info>Compiling one template</info>');
        $this->compileTemplate($fileName);
    }

    /**
     * Walk over $iterator and compile all templates in it.
     *
     * @param mixed $iterator Iterator to walk over.
     *
     * @return void
     */
    protected function walkIterator($iterator)
    {
        foreach ($iterator as $template) {
            $this->compileTemplate($template);
        }
    }

    /**
     * Compile a template.
     *
     * @param string $fileName Template to compile.
     *
     * @return void
     */
    protected function compileTemplate($fileName)
    {
        try {
            $this->
                twigView->
                getTwig()->
                loadTemplate($fileName);
            $this->out('<success>' . $fileName . '</success>');
        } catch (\Exception $exception) {
            $this->out('<error>' . $fileName . '</error>');
            $this->out('<error>' . $exception->getMessage() . '</error>');
        }
    }

    /**
     * Set options for this console.
     *
     * @return \Cake\Console\ConsoleOptionParser
     */
    // @codingStandardsIgnoreStart
    public function getOptionParser()
    {
        // @codingStandardsIgnoreEnd
        return parent::getOptionParser()->addSubcommand(
            'all',
            [
                'short' => 'a',
                // @codingStandardsIgnoreStart
                'help' => __('Searches and precompiles all twig templates it finds.')
                // @codingStandardsIgnoreEnd
            ]
        )->addSubcommand(
            'plugin',
            [
                'short' => 'p',
                // @codingStandardsIgnoreStart
                'help' => __('Searches and precompiles all twig templates for a specific plugin.')
                // @codingStandardsIgnoreEnd
            ]
        )->addSubcommand(
            'file',
            [
                'short' => 'f',
                // @codingStandardsIgnoreStart
                'help' => __('Precompile a specific file.')
                // @codingStandardsIgnoreEnd
            ]
        // @codingStandardsIgnoreStart
        )->setDescription(__('TwigView templates precompiler'));
        // @codingStandardsIgnoreEnd
    }
}
