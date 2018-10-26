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

use Cake\Event\EventListenerInterface;
use WyriHaximus\TwigView\Lib\Cache;
use WyriHaximus\TwigView\Lib\Twig\Extension;
use WyriHaximus\TwigView\Lib\Twig\TokenParser;

/**
 * Class TokenParsersListener
 * @package WyriHaximus\TwigView\Event
 */
class TokenParsersListener implements EventListenerInterface
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
        // CakePHP specific tags
        $event->getTwig()->addTokenParser(new TokenParser\Cell);
        $event->getTwig()->addTokenParser(new TokenParser\Element);
        // @codingStandardsIgnoreEnd
    }
}
