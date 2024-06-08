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

abstract class Type extends FFI
{
    private $cobj;
    private $dim = 0;
    final public function __construct($v = null, $owned = true, $persistent = false)
    {
        $cdef = self::getCName();
        if (is_array($v)) {
            $this->dim = $this->arrayDim($v);
            $cdef = self::arrayType($cdef, $this->dim)->getName();
            $this->cobj = $this->new($cdef, $owned, $persistent);
            $this->fillArray($v, $this->cobj);
        } else {
            $this->cobj = $this->new($cdef, $owned, $persistent);
            $this->cobj->cdata = $v;
        }
    }


    public static function getCName(?ReflectionClass $refl = null)
    {
        $refl = self::reflectionClass($refl);
        $parent = $refl->getParentClass()->name;
        if($refl->hasConstant('NAME') && $parent == Type::class) {
            return $refl->getConstant('NAME');
        } else if($parent == Callback::class) {
            return $refl->getShortName();
        } else if($parent != Type::class){
            return $parent::getCName();
        }
        return $refl->getShortName();
    }

    public static function getDeclared()
    {
        return '';
    }

    public function getData()
    {
        return $this->cobj->cdata;
    }

    public static function ptr($v, $owned = true, $persistent = false)
    {
        $c = new static($v, $owned, $persistent);
        $c->cobj = self::addr($c->cobj);
        return $c;
    }
    protected function fillArray($value, &$cdata)
    {
        foreach ($value as  $i => $v) {
            if (is_array($v)) {
                $cdata[$i] = $this->fillArray($v, $cdata[$i]);
            } else {
                $cdata[$i] = $v;
            }
        }
    }
    protected function arrayDim(array $v)
    {
        $dim = [];
        do {
            $dim[] = count($v);
            $v = $v[0];
        } while (is_array($v));
        return $dim;
    }

    public static function getDef(&$requireType = [])
    {
        $refl = self::reflectionClass();

        if($refl->getParentClass()->name == Type::class) {
            return '';
        }
        $def = self::TYPEDEF . self::SPACE;
        if ($refl->isSubclassOf(Callback::class)) {
            return $def . self::getCallbackDef($refl, $requireType);
        }

        if ($refl->implementsInterface(Unsigned::class)) {
            $def .=  Unsigned::ID_NAME . self::SPACE;
        } else {
            $def .= Signed::ID_NAME . self::SPACE;
        }
        if ($refl->implementsInterface(Long::class)) {
            $def .= Long::ID_NAME . self::SPACE;
        } else if ($refl->implementsInterface(Short::class)) {
            $def .=  Short::ID_NAME . self::SPACE;
        }
        if($refl->getParentClass() != Type::class) {
            $requireType[] = $refl->name;
        }
        $def .= $refl->name::getCName();
        $def .= self::SPACE . $refl->getShortName() . ';';
        return $def;
    }

    public static function getCallbackDef(?ReflectionClass $refl = null, &$requireType = [])
    {
        $refl = self::reflectionClass($refl);
        $method = $refl->getMethod($refl->getShortName());
        return  self::parseFunction($method, true, $requireType);
    }
}


class Char extends Type
{
    const NAME = 'char';
}
class Int32 extends Type
{
    const NAME = 'int';
}

class Float32 extends Type
{
    const NAME = 'float';
}

class FLoat64 extends Type
{
    const NAME = 'double';
}

class VoidPointer extends Type
{
    const NAME = 'void*';
}

class LongInt extends Type
{
    const NAME = 'long';
}

abstract class Callback extends Type
{
}
