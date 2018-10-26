<?php

/**
 * This file is part of TwigView.
 *
 ** (c) 2014 Cees-Jan Kiewiet
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace WyriHaximus\TwigView\Event;

use Aptoma\Twig\Extension\MarkdownEngineInterface;
use Aptoma\Twig\Extension\MarkdownExtension;
use Aptoma\Twig\TokenParser\MarkdownTokenParser;
use Ajgl\Twig\Extension\BreakpointExtension;
use Asm89\Twig\CacheExtension\CacheStrategy\LifetimeCacheStrategy;
use Asm89\Twig\CacheExtension\Extension as CacheExtension;
use Cake\Core\Configure;
use Cake\Event\EventListenerInterface;
use Jasny\Twig\ArrayExtension;
use Jasny\Twig\DateExtension;
use Jasny\Twig\PcreExtension;
use Jasny\Twig\TextExtension;
use Umpirsky\Twig\Extension\PhpFunctionExtension;
use WyriHaximus\TwigView\Lib\Cache;
use WyriHaximus\TwigView\Lib\Twig\Extension;
use WyriHaximus\TwigView\Lib\Twig\TokenParser;

/**
 * Class ExtensionsListener
 * @package WyriHaximus\TwigView\Event
 */
class ExtensionsListener implements EventListenerInterface
{
    /**
     * Return implemented events.
     *
     * @return array
     */
    public function implementedEvents()
    {
        return [
            ConstructEvent::EVENT => 'construct',
        ];
    }

    /**
     * Event handler.
     *
     * @param ConstructEvent $event Event.
     *
     * @return void
     */
    // @codingStandardsIgnoreStart
    public function construct(ConstructEvent $event)
    {
        // @codingStandardsIgnoreEnd
        // @codingStandardsIgnoreStart
        // Twig core extensions
        $event->getTwig()->addExtension(new \Twig_Extension_StringLoader);
        $event->getTwig()->addExtension(new \Twig_Extension_Debug);

        // CakePHP bridging extensions
        $event->getTwig()->addExtension(new Extension\I18n);
        $event->getTwig()->addExtension(new Extension\Time);
        $event->getTwig()->addExtension(new Extension\Basic);
        $event->getTwig()->addExtension(new Extension\Number);
        $event->getTwig()->addExtension(new Extension\Utils);
        $event->getTwig()->addExtension(new Extension\Arrays);
        $event->getTwig()->addExtension(new Extension\Strings);
        $event->getTwig()->addExtension(new Extension\Inflector);

        if (
            !Configure::check('WyriHaximus.TwigView.flags.potentialDangerous') ||
            (
                Configure::check('WyriHaximus.TwigView.flags.potentialDangerous') &&
                Configure::read('WyriHaximus.TwigView.flags.potentialDangerous') === true
            )
        ) {
            $event->getTwig()->addExtension(new Extension\PotentialDangerous);
        }

        // Markdown extension
        if (
            Configure::check('WyriHaximus.TwigView.markdown.engine') &&
            Configure::read('WyriHaximus.TwigView.markdown.engine') instanceof MarkdownEngineInterface
        ) {
            $engine = Configure::read('WyriHaximus.TwigView.markdown.engine');
            $event->getTwig()->addExtension(new MarkdownExtension($engine));
            $event->getTwig()->addTokenParser(new MarkdownTokenParser($engine));
        }

        // Third party cache extension
        $cacheProvider = new Cache();
        $cacheStrategy = new LifetimeCacheStrategy($cacheProvider);
        $cacheExtension = new CacheExtension($cacheStrategy);
        $event->getTwig()->addExtension($cacheExtension);

        // jasny/twig-extensions
        $event->getTwig()->addExtension(new DateExtension());
        $event->getTwig()->addExtension(new PcreExtension());
        $event->getTwig()->addExtension(new TextExtension());
        $event->getTwig()->addExtension(new ArrayExtension());

        // umpirsky/twig-php-function
        $event->getTwig()->addExtension(new PhpFunctionExtension());

        // Breakpoint extension
        if (Configure::read('debug') === true) {
            $event->getTwig()->addExtension(new BreakpointExtension());
        }

        // @codingStandardsIgnoreEnd
    }
}
