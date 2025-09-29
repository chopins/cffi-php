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
use ReflectionProperty;
use ReflectionUnionType;
use ReflectionIntersectionType;
use ReflectionNamedType;
use CFFI\Type;
use CFFI\CType\CArray;
use CFFI\CType\Pointer;
use CFFI\CType\Unsigned;
use CFFI\CType\Signed;

class Struct extends Type
{
    const NAME = 'struct';
    private $cobj;
    public function __construct(array $v = [], $owned = true, $persistent = false)
    {
        $cdef = self::getCName();
        $this->cobj = $this->new($cdef, $owned, $persistent);
        foreach ($v as $k => $value) {
            $this->cobj->cdata->$k = $value;
        }
    }

    public static function getTypedef(): string
    {
        $cname = self::getCName();
        $typedef = parent::NAME . self::SPACE . self::NAME . " _$cname {";
        $typedef .= self::getMemberStatement(static::class);
        return $typedef . "} $cname;";
    }

    public static function getMemberStatement($className)
    {
        $refCls = new ReflectionClass($className);
        $properties = $refCls->getProperties(ReflectionProperty::IS_STATIC);
        $statement = '';
        foreach ($properties as $property) {
            if ($property->isPublic() && $property->hasType()) {
                $statement .= self::getTypeStatement($property) . ';';
            }
        }
        return $statement;
    }

    public function __set($name, $value)
    {
        $this->cobj->cdata->$name = $value;
    }

    public function __get($name)
    {
        $this->cobj->cdata->$name;
    }
}
