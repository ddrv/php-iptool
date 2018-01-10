<?php

namespace Ddrv\Iptool\Wizard;

/**
 * Class TypeAbstract
 */
abstract class TypeAbstract
{
    public function getValidValue($value)
    {
        return $value;
    }
}