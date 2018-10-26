<?php
namespace Aura\Intl;

class MockFormatter implements FormatterInterface
{
    public function format($locale, $string, array $tokens_values = [])
    {
        return $string;
    }
}
