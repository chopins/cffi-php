<?php

/**
 * cffi-php (http://toknot.com)
 *
 * @copyright  Copyright (c) 2019 Szopen Xiao (Toknot.com)
 * @license    http://toknot.com/LICENSE.txt New BSD License
 * @link       https://github.com/chopins/cffi-php
 */

namespace CFFI;

class Union extends Struct
{
    const NAME = 'union';
    public static function getTypedef(): string
    {
        $cname = self::getCName();
        $typedef = self::NAME . " _$cname {";
        $typedef .= self::getMemberStatement(static::class);
        return $typedef . "};";
    }
}
