<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigTest;

class CustomTestExtension extends AbstractExtension
{
    public function getTests()
    {
        return [
            new TwigTest('string', [$this, 'isString']),
        ];
    }

    public function isString($value)
    {
        return is_string($value);
    }
}
