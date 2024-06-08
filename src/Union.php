<?php

/**
 * cffi-php (http://toknot.com)
 *
 * @copyright  Copyright (c) 2019 Szopen Xiao (Toknot.com)
 * @license    http://toknot.com/LICENSE.txt New BSD License
 * @link       https://github.com/chopins/cffi-php
 */

namespace CFFI;

use ReflectionClass;

class Union extends Struct
{
    const NAME = 'union';
    public static function getDef(&$requireType = []): string
    {
        $refl = self::reflectionClass();
        $name = self::getCName($refl);
        $member = self::getMemberDef($refl, $requireType);
        return self::TYPEDEF . self::SPACE . self::NAME . " $name { $member } $name;";
    }
}
