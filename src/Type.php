<?php

/**
 * cffi-php (http://toknot.com)
 *
 * @copyright  Copyright (c) 2019 Szopen Xiao (Toknot.com)
 * @license    http://toknot.com/LICENSE.txt New BSD License
 * @link       https://github.com/chopins/cffi-php
 */


namespace CFFI;

use CFFI\CType\Unsigned;
use CFFI\CType\Signed;
use CFFI\CType\Callback;
abstract class Type extends FFI
{
    private $cobj;
    private $dim = 0;
    const NAME = 'typedef';
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

    public static function getTypedef(): string
    {
        $className = static::class;
        $cname = self::getCName();
        $parents = class_parents($className);
        $modifiers = class_implements($className);
        $baseType = '';
        foreach ($parents as $p) {
            if (strpos($p, __NAMESPACE__ . '\CType') === 0) {
                $baseType = $p::NAME;
                break;
            }
        }
        if (empty($baseType)) {
            throw new \TypeError("Undefined type");
        }

        $signed = '';
        foreach ($modifiers as $modifier) {
            if ($modifier == Unsigned::class) {
                $signed = Unsigned::NAME;
            } else if ($modifier == Signed::class) {
                $signed = Signed::NAME;
            } else if ($modifier == Callback::class) {
                return self::getFucntionTypedef($cname);
            }
        }
        return self::NAME . " $signed $baseType $cname;";
    }

    public static function getFucntionTypedef(string $cname): string
    {
        $refMethod = new \ReflectionMethod(static::class, '__invoke');
        $retTypeName = '';
        $paramsType = self::parseFucntionModifier($refMethod, $retTypeName);
        return self::NAME . " $retTypeName (*$cname)($paramsType)";
    }


    /**
     * get C type name from Class::NAME
     * php class name:A\B\C   C type name: A_A_A
     * php class name:A\B\CName    C type name: A_B_CName
     */
    public static function getCName()
    {
        $className = static::class;
        if (defined("$className::NAME")) {
            $cname = $className::NAME;
        } else {
            $cname = str_replace('\\', '_', $className);
        }
        return $cname;
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
}
