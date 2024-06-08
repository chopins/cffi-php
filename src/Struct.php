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

class Struct extends FFI
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

    public static function getCName(?ReflectionClass $refl = null)
    {
        $refl = self::reflectionClass($refl);
        return $refl->getShortName();
    }

    public static function getDeclared()
    {
        $name = static::getCName();
        return self::TYPEDEF . self::SPACE . self::NAME . " $name $name;";
    }

    public function __set($name, $value)
    {
        $this->cobj->cdata->$name = $value;
    }

    public function __get($name)
    {
        $this->cobj->cdata->$name;
    }

    public static function getDef(&$requireType = []): string
    {
        $refl = self::reflectionClass();
        $name = self::getCName($refl);
        $lastReq = $requireType;
        $member = self::getMemberDef($refl, $requireType);
        if (empty($member)) {
            $parent = $refl->getParentClass();
            $pn = $parent->name;
            if ($parent->getParentClass() != Type::class) {
                $requireType[] = $pn;
            }
            return self::TYPEDEF . self::SPACE . self::NAME . self::SPACE . $pn::getCName() . self::SPACE . "$name;";
        }
        $prefix = '';
        if (array_search($refl->name, array_diff_assoc($requireType, $lastReq)) !== false) {
            $prefix = self::TYPEDEF . self::SPACE . self::NAME . " $name $name;";
        }
        return $prefix . self::TYPEDEF . self::SPACE . self::NAME . " $name { $member } $name;";
    }
    public static function getMemberDef(ReflectionClass $ref = null, &$requireType = [])
    {
        $ref = self::reflectionClass($ref);
        $properties = $ref->getProperties(ReflectionProperty::IS_PRIVATE);
        $define = '';
        foreach ($properties as $p) {
            $type = $p->getType();
            $pname = $p->name;
            if ($type instanceof ReflectionUnionType) {
                foreach ($type->getTypes() as $t) {
                    $tname = $t->getName();
                    if (is_subclass_of($tname, Type::class)) {
                        $typeName = $tname;
                        break;
                    }
                }
            } else {
                $typeName = $type->getName();
            }

            if (is_subclass_of($typeName, Callback::class)) {
                $define .= str_replace(self::TYPEDEF, '', $typeName::getDef($requireType));
                continue;
            } else {
                $ctype = $typeName::getCName();
            }
            if (get_parent_class($typeName) != Type::class) {
                $requireType[] = $typeName;
            }

            $value = $p->getDefaultValue();
            $array = '';
            if (is_array($value)) {
                foreach ($value as $v) {
                    $array .= "[$v]";
                }
            } else if (is_int($value)) {
                $ctype .= str_repeat('*', $value);
            }
            $define .= "$ctype {$pname}{$array};";
        }

        return $define;
    }
}
