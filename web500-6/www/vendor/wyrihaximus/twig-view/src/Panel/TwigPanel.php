<?php

namespace WyriHaximus\TwigView\Panel;

use DebugKit\DebugPanel;
use WyriHaximus\TwigView\Lib\TreeScanner;

// @codingStandardsIgnoreStart
class TwigPanel extends DebugPanel
// @codingStandardsIgnoreEnd
{
    /**
     * Plugin name.
     *
     * @var string
     */
    // @codingStandardsIgnoreStart
    public $plugin = 'WyriHaximus/TwigView';
    // @codingStandardsIgnoreEnd

    /**
     * Get the data for the twig panel.
     *
     * @return array
     */
    public function data()
    {
        return [
            'templates' => TreeScanner::all(),
        ];
    }
}
