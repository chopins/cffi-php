<?php

/**
 * cffi-php (http://toknot.com)
 *
 * @copyright  Copyright (c) 2019 Szopen Xiao (Toknot.com)
 * @license    http://toknot.com/LICENSE.txt New BSD License
 * @link       https://github.com/chopins/cffi-php
 */

namespace CFFI;

use FFI as ExtFFI;
use FFI\CData as ExtCData;
use FFI\CType as ExtCType;
use ReflectionAttribute;
use ReflectionClass;
use ReflectionMethod;
use ReflectionUnionType;

abstract class FFI
{
    const TYPEDEF = 'typedef';
    const SPACE = ' ';
    const COMMA = ',';
    public static function cdef($code = '', $lib = null): ExtFFI
    {
        return ExtFFI::cdef($code, $lib);
    }

    public function new($type, $owned = true, $persistent = false): ?ExtCData
    {
        return ExtFFI::new($type, $owned, $persistent);
    }

    public static function arrayType($type, $dim): ExtCType
    {
        return ExtFFI::arrayType($type, $dim);
    }
    public static function sizeof(ExtCData|ExtCType &$ptr): int
    {
        return ExtFFI::sizeof($ptr);
    }
    public static function addr(ExtCData &$ptr): ExtCData
    {
        return ExtFFI::addr($ptr);
    }

    public static function alignof(ExtCData|ExtCType &$ptr): int
    {
        return ExtFFI::alignof($ptr);
    }

    public static function cast(ExtCType|string $type, ExtCData|int|float|bool|null &$ptr): ?ExtCData
    {
        return ExtFFI::cast($type, $ptr);
    }

    public static function free(ExtCData &$ptr): void
    {
        ExtFFI::free($ptr);
    }

    public static function reflectionClass(ReflectionClass $refl = null): ReflectionClass
    {
        return $refl ?? new ReflectionClass(get_called_class());
    }

    public static function parseFunction(ReflectionMethod $m, $callback = false, &$requireType = []): string
    {
        $fnCdef = '';
        $attr = $m->getAttributes(CCallType::class, ReflectionAttribute::IS_INSTANCEOF);
        if ($attr) {
            $fnCdef .= constant("{$attr[0]}::NAME");
        }

        $reflReturnType = $m->getReturnType();
        $returnType = $reflReturnType->getName();

        if(!$reflReturnType->isBuiltin() && get_parent_class($returnType) != Type::class) {
            $requireType[] = $returnType;
        }
        if (is_subclass_of($returnType, ReturnPtr::class)) {
            $fnCdef .= get_parent_class($returnType)::getCName();
            $fnCdef .= $returnType::ptrLevel();
        } else if ($reflReturnType->isBuiltin() && in_array($returnType, ['void', 'int', 'double', 'float'])) {
            $fnCdef .= $returnType;
        } else {
            $fnCdef .= $returnType::getCName();
        }

        if ($m->returnsReference()) {
            $fnCdef .= '*';
        }
        if ($callback) {
            $fnCdef .= ' (*' . $m->name . ')(';
        } else {
            $fnCdef .= ' ' . $m->name . '(';
        }
        $fparam = $m->getParameters();
        $comma = '';

        foreach ($fparam as $p) {
            $ptype = $p->getType();
            if ($ptype instanceof ReflectionUnionType) {
                foreach ($ptype->getTypes() as $t) {
                    $tname = $t->getName();
                    if (is_subclass_of($tname, Type::class)) {
                        $typeName = $tname;
                        break;
                    }
                }
            } else {
                $typeName = $ptype->getName();
            }

            if(get_parent_class($typeName) != Type::class) {
                $requireType[] = $typeName;
            }
            $fnCdef .= $comma . $typeName::getCName() . self::SPACE;
            $dfv = null;
            if ($p->isDefaultValueAvailable()) {
                $dfv = $p->getDefaultValue();
            }
            if (is_int($dfv)) {
                $fnCdef .= str_repeat('*', $dfv);
            } else if ($p->isPassedByReference()) {
                $fnCdef .= '*';
            }
            $fnCdef .= $p->name;
            if ($p->isVariadic()) {
                $fnCdef .= '...';
            }
            $comma = self::COMMA;
        }

        return $fnCdef . ');';
    }
}
