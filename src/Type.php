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
use CFFI\CType\CArray;
use CFFI\CType\Pointer;
use CFFI\CType\Unsigned;
use CFFI\CType\Signed;
use CFFI\CType\Callback;
use ReflectionProperty;
use ReflectionParameter;
use ReflectionNamedType;
use ReflectionIntersectionType;

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

    public static function nameType2CName(ReflectionNamedType $type, &$typeClass): string
    {
        $typeClass = $type->getName();
        if ($typeClass instanceof Type) {
            return $typeClass::getCName();
        }
        return '';
    }

    public static function parseReflectionTypeModifier($types, &$cname, &$signed, &$pointer)
    {
        $signed = $pointer = $typeClass = '';
        if ($types instanceof \ReflectionNamedType) {
            $cname = self::nameType2CName($types, $typeClass);
        } else if ($types instanceof \ReflectionIntersectionType) {
            $cname = '';
            foreach ($types as $t) {
                $tCName = self::nameType2CName($t, $typeClass);
                if ($tCName) {
                    $cname = $tCName;
                } else if ($typeClass == Unsigned::class) {
                    $signed = Unsigned::NAME;
                } else if ($typeClass == Signed::class) {
                    $signed = Signed::NAME;
                } else if ($typeClass instanceof Pointer) {
                    $pointer = $typeClass::NAME;
                }
            }
        }
    }

    public static function getFucntionTypedef($cname): string
    {
        $refMethod = new \ReflectionMethod(static::class, '__invoke');
        $retType = $refMethod->getReturnType();

        $cname = $signed = $pointer = '';
        self::parseReflectionTypeModifier($retType, $cname, $signed, $pointer);
        if (!$cname) {
            throw new \TypeError('must have return type');
        }
        $retTypeName = "$signed $cname $pointer";

        $params = $refMethod->getParameters();
        $paramsType = '';
        foreach ($params as $refParam) {
            if (!$refParam->hasType()) {
                throw new \TypeError('function parmaters must have type');
            }
            if ($refParam->isVariadic()) {
                $paramsType .= '...';
            } else {
                $paramsType .= self::getTypeStatement($refParam) . ',';
            }
        }
        $paramsType = rtrim($paramsType, ',');

        return self::NAME . " $retTypeName (*$cname)($paramsType)";
    }

    public static function getTypeStatement(ReflectionProperty|ReflectionParameter $scope = null): string
    {
        $variableName = $scope->name;
        $type = $scope->getType();
        $cname = $signed = $pointer = $carray = '';
        self::parseReflectionTypeModifier($type, $cname, $signed, $pointer);
        if (!$cname) {
            return '';
        }

        while ($level = $scope->getAttributes(Pointer::class)) {
            if (!($level = $level[0]->getArguments())) {
                $pointer = Pointer::NAME;
                break;
            }
            if ($level[0] > 0) {
                $pointer = str_repeat(Pointer::NAME, $level[0]);
                break;
            } else {
                throw new \TypeError('Pointer level must greater than 0');
            }
        }
        while ($size = $scope->getAttributes(CArray::class)) {
            if (!($size = $size[0]->getArguments())) {
                throw new \TypeError('C array must have size');
            }
            if ($size[0] > 0) {
                $carray = "[{$size[0]}]";
                break;
            } else {
                throw new \TypeError('C array size must greater than 0');
            }
        }
        return "$signed $cname $pointer $variableName $carray";
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


    public static function getCallbackDef(?ReflectionClass $refl = null, &$requireType = [])
    {
        $refl = self::reflectionClass($refl);
        $method = $refl->getMethod($refl->getShortName());
        return  self::parseFunction($method, true, $requireType);
    }
}
