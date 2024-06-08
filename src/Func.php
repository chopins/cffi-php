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
use ReflectionClassConstant;
use ReflectionMethod;

class Func extends FFI
{
    private $ffiobj;
    final public function __construct($lib)
    {
        $refl = self::reflectionClass();
        $code = self::getStructs($refl);
        $code .= self::getEnum($refl);
        $code .= self::getVariable($refl);
        $code .= self::getFunctions($refl);

        $this->ffiobj = self::cdef($code, $lib);
    }

    public static function getVariable(ReflectionClass $classRefl = null)
    {
        $refl = self::reflectionClass($classRefl);
        return Struct::getMemberDef($refl);
    }
    public static function getEnum(ReflectionClass $classRefl = null)
    {
        $refl = self::reflectionClass($classRefl);
        $constants = $refl->getConstants(ReflectionClassConstant::IS_PRIVATE);
        $enumDef = '';
        foreach ($constants as $n => $constant) {
            if (is_array($constant)) {
                $enumDef .= "enum $n {";
                $comma = '';
                foreach ($constant as $k => $v) {
                    $enumDef .= "{$comma}$v = $k";
                    $comma = self::COMMA;
                }
                $enumDef .= '};';
            }
        }
        return $enumDef;
    }

    public static function getStructs(ReflectionClass $classRefl = null)
    {
        $refl = self::reflectionClass($classRefl);
        $namespace = $refl->getNamespaceName();
        $classList = get_declared_classes();
        $structDef = [];

        $requireType = [];
        $deps = [];
        foreach ($classList as $i=> $name) {
            if (strpos($name, $namespace) === 0 && (is_subclass_of($name, Struct::class) || is_subclass_of($name, Type::class))) {
                if (class_parents($name) == Type::class) {
                    continue;
                }
                $def = $name::getDef($requireType);

                $requireType = array_unique($requireType);
                $diff = array_diff($requireType, array_keys($structDef));
                
                if($diff && ($requireType = array_diff($diff, [$name]))) {
                    $deps[$name] = $def;
                } else {
                    $structDef[$name] = $def;
                    $structDef = array_merge($structDef, $deps);
                    $deps = [];
                }
            }
        }
        $structDef = array_merge($structDef, $deps);
        return join('', $structDef);
    }

    public static function getFunctions(ReflectionClass $classRefl = null)
    {
        $refl = self::reflectionClass($classRefl);
        $methods = $refl->getMethods(ReflectionMethod::IS_PRIVATE);
        $fnCdef = '';
        foreach ($methods as $m) {
            $fnCdef .= self::parseFunction($m);
        }
        return $fnCdef;
    }

    public function __call($name, $arguments)
    {
        $arguments = array_values($arguments);
        return $this->ffiobj->$name(...$arguments);
    }

    public function __set($name, $value)
    {
        $this->ffiobj->$name = $value;
    }

    public function __get($name)
    {
        return $this->ffiobj->$name;
    }
}
