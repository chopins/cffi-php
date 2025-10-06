<?php

/**
 * cffi-php (http://toknot.com)
 *
 * @copyright  Copyright (c) 2019 Szopen Xiao (Toknot.com)
 * @license    http://toknot.com/LICENSE.txt New BSD License
 * @link       https://github.com/chopins/cffi-php
 */

namespace CFFI;

use ReflectionType;
use ReflectionNamedType;
use ReflectionIntersectionType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionObject;
use ReflectionMethod;
use BadMethodCallException;
use FFI as ExtFFI;
use FFI\CData;
use FFI\CType;
use CFFI\CType\Pointer;
use CFFI\CType\Unsigned;
use CFFI\CType\Signed;
use CFFI\CType\CArray;
use CFFI\CType\Extern;
use CFFI\CType\Stdcall;
use CFFI\CType\Vectorcall;
use CFFI\CType\Fastcall;


abstract class FFI
{
    private $ffiInstance;

    private static $typeClassList = [];
    private static $typeClassOrder = 0;
    private $cfunction = [];

    final public function __construct(string $lib = null)
    {
        $this->initFFIObject($lib);
    }

    final public function __call($name, $arguments)
    {
        if (in_array($name, $this->cfunction)) {
            $this->ffiInstance->$name(...$arguments);
        }
        throw new BadMethodCallException("Call to undefined method " . $this::class . ":{$name}()");
    }

    final public function __get($name)
    {
        return $this->ffiInstance->$name;
    }

    final public function __set($name, $value)
    {
        $this->ffiInstance->$name = $value;
    }

    public function new($type, $owned = true, $persistent = false): ?CData
    {
        return $this->ffiInstance->new($type, $owned, $persistent);
    }

    public static function arrayType($type, $dim): CType
    {
        return ExtFFI::arrayType($type, $dim);
    }
    public static function sizeof(CData|CType &$ptr): int
    {
        return ExtFFI::sizeof($ptr);
    }
    public static function addr(CData &$ptr): CData
    {
        return ExtFFI::addr($ptr);
    }

    public static function alignof(CData|CType &$ptr): int
    {
        return ExtFFI::alignof($ptr);
    }

    public function cast(CType|string $type, CData|int|float|bool|null &$ptr): ?CData
    {
        return $this->ffiInstance->cast($type, $ptr);
    }

    public static function free(CData &$ptr): void
    {
        ExtFFI::free($ptr);
    }

    public function getFFI()
    {
        return $this->ffiInstance;
    }

    private function initFFIObject(string $lib): void
    {
        self::$typeClassList = [];
        self::$typeClassOrder = [];
        $cdef = $this->parseFunctionCDef();
        $typedef = $this->parseTypeCDef();
        $this->ffiInstance = ExtFFI::cdef($typedef . $cdef, $lib);
    }

    private function parseTypeCDef(): string
    {
        $alias = $type = $struct = '';
        foreach (self::$typeClassList as $class => $order) {
            if ($class instanceof Struct) {
                $cname = $class::getCName();
                $struct .= $class::getTypedef();
                $alias .= "typedef _{$cname} $cname;";
            } else {
                $type .= $class::getTypedef();
            }
        }
        return $type . $alias . $struct;
    }

    private function parseFunctionCDef(): string
    {
        self::$typeClassList = [];
        $refObj = new ReflectionObject($this);
        $cdef = $this->parseVariableCDef($refObj);
        $methods = $refObj->getMethods(ReflectionMethod::IS_PRIVATE);
        foreach ($methods as $method) {
            if ($method->isStatic()) {
                continue;
            }
            $functionModifier = '';
            if ($method->getAttributes(Extern::class)) {
                $functionModifier = Extern::NAME;
            } else if ($method->getAttributes(Stdcall::class)) {
                $functionModifier = Stdcall::NAME;
            } else if ($method->getAttributes(Fastcall::class)) {
                $functionModifier = Fastcall::NAME;
            } else if ($method->getAttributes(Vectorcall::class)) {
                $functionModifier = Vectorcall::NAME;
            } else {
                continue;
            }

            $paramsType = self::parseFucntionModifier($method, $retTypeName);
            $cdef .= "$functionModifier $retTypeName {$method->name}($paramsType);";
            $this->cfunction[] = $method->name;
        }
        return $cdef;
    }

    private function parseVariableCDef(ReflectionObject $refObj): string
    {
        $properties = $refObj->getProperties(ReflectionProperty::IS_PRIVATE);
        $variable = '';
        foreach ($properties as $property) {
            if ($property->isStatic()) {
                continue;
            }
            if ($property->getAttributes(Extern::class)) {
                $variable .= self::getTypeStatement($property) . ';';
            }
        }
        return $variable;
    }

    public static function nameType2CName(ReflectionNamedType $type, string &$typeClass): string
    {
        $typeClass = $type->getName();
        if ($typeClass instanceof Type) {
            self::$typeClassList[$typeClass] = self::$typeClassOrder;
            self::$typeClassOrder++;
            return $typeClass::getCName();
        }
        return '';
    }

    public static function parseFucntionModifier(ReflectionMethod $refMethod, string &$retTypeName): string
    {
        $retType = $refMethod->getReturnType();
        $signed = $pointer = '';
        $cname = self::parseReflectionTypeModifier($retType, $signed, $pointer);
        if (!$cname) {
            throw new \TypeError('must have return type');
        }
        if(empty($pointer) && $refMethod->returnsReference()) {
            $pointer = Pointer::NAME;
        }
        $retTypeName = "$signed $pointer";

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
        return rtrim($paramsType, ',');
    }

    public static function parseReflectionTypeModifier(ReflectionType $types, string &$signed, string &$pointer): string
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
        return $cname;
    }

    public static function getTypeStatement(ReflectionProperty|ReflectionParameter $scope): string
    {
        $variableName = $scope->name;
        $type = $scope->getType();
        $signed = $pointer = $carray = '';
        $cname = self::parseReflectionTypeModifier($type, $signed, $pointer);
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
        if(empty($pointer) && $scope instanceof ReflectionParameter && $scope->isPassedByReference()) {
            $pointer = Pointer::NAME;
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
}
