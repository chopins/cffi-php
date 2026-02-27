<?php

/**
 * cffi-php (http://toknot.com)
 *
 * @copyright  Copyright (c) 2019 - 2026 Szopen Xiao (Toknot.com)
 * @license    http://toknot.com/LICENSE.txt New BSD License
 * @link       https://github.com/chopins/cffi-php
 */

namespace CFFI;

interface CFFI {}

#[\Attribute(\Attribute::TARGET_PROPERTY | \Attribute::TARGET_PARAMETER)]
interface Pointer extends CFFI
{
    const KEY = '*';
}

interface _ extends Pointer
{
    const KEY = '*';
}

interface __ extends Pointer
{
    const KEY = '**';
}

interface ___ extends Pointer
{
    const KEY = '***';
}
interface ____ extends Pointer
{
    const KEY = '****';
}
interface Signed extends CFFI
{
    const KEY = 'signed';
}

interface Unsigned extends CFFI
{
    const KEY = 'unsigned';
}

#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::TARGET_PROPERTY)]
enum CallingConvention: string implements CFFI
{
    case Extern = 'extern';
    case Vectorcall = '__vectorcall';
    case Stdcall = '__stdcall';
    case Fastcall = '__fastcall';
    case Cdecl = '__cdecl';
}

enum ByteOrder implements CFFI
{
    case HOST_BYTE_ORDER;
    case LITTLE_ENDIAN_ORDER;
    case BIG_ENDIAN_ORDER;
    case NETWORK_BYTE_ORDER;
}

abstract class Type implements CFFI
{
    const NAME = 'typedef';
    const BASE_TYPE = 0;
    const HOST_BYTE_ORDER = 0;
    const NETWORK_BYTE_ORDER = 2;
    const LITTLE_ENDIAN_ORDER = 1;
    const BIG_ENDIAN_ORDER = 2;

    const PACK_CODE = '';
    const PACK_U_CODE = '';
    const PACK_UB_CODE = '';
    const PACK_UL_CODE = '';
    const PACK_L_CODE = '';
    const PACK_B_CODE = '';

    const C_INT = 'int';
    const C_CHAR = 'char';
    const C_SHORT = 'short';
    const C_LONG = 'long';
    const C_DOUBLE = 'double';
    const C_FLOAT = 'float';
    const C_LONG_DOUBLE = 'long double';
    const C_VOID = 'void';
    const C_ARRAY = 'array';
    private $cvalue;
    private static $defaultByteOrder = ByteOrder::HOST_BYTE_ORDER;

    public  function __construct($value = null, bool $owned = true, bool $persistent = false)
    {
        $ffi = $this->getFFI();
        $this->cvalue = $ffi->new(static::NAME, $owned, $persistent);
        $this->cvalue->cdata = $value;
    }

    public function getValue()
    {
        return $this->cvalue;
    }

    public function getFFI()
    {
        return \FFI::cdef();
    }

    public static function getTypedef(array &$depsType = []): void
    {
        if (static::BASE_TYPE) {
            return;
        }
        $unsigned = static::class instanceof Unsigned ? Unsigned::KEY : '';
        $pclass = get_parent_class(static::class);
        $depsType[static::NAME] = self::NAME . " $unsigned " . $pclass::NAME . ' ' . static::NAME . PHP_EOL;
        return;
    }
    public static function setDefaultByteOrder(ByteOrder $byteOrder)
    {
        self::$defaultByteOrder = $byteOrder;
    }
    public function unpack(string $binary, ?ByteOrder $byteOrder = null): array|false
    {
        return unpack($this->getPackCode($byteOrder), $binary);
    }
    public function pack(?ByteOrder $byteOrder = null): string
    {
        return pack($this->getPackCode($byteOrder), $this->getCData());
    }
    public static function getPackCode(?ByteOrder $byteOrder = null): string
    {
        if ($byteOrder === null) {
            $byteOrder = self::$defaultByteOrder;
        }

        $unsigned = static::class instanceof Unsigned;
        if (!$unsigned) {
            return static::PACK_CODE;
        }
        if (static::NAME == self::C_FLOAT || static::NAME == self::C_DOUBLE) {
            if ($byteOrder == ByteOrder::LITTLE_ENDIAN_ORDER) {
                return static::PACK_L_CODE;
            } else if ($byteOrder == ByteOrder::BIG_ENDIAN_ORDER) {
                return static::PACK_B_CODE;
            }
        }
        if ($byteOrder == ByteOrder::LITTLE_ENDIAN_ORDER) {
            return static::PACK_UL_CODE;
        } else if ($byteOrder == ByteOrder::BIG_ENDIAN_ORDER) {
            return static::PACK_UB_CODE;
        }
        return static::PACK_U_CODE;
    }

    final public static function getType(\ReflectionType $type, array &$depsType = [], bool &$hasArray = false): string
    {
        $ptypes = explode('|', $type->__toString());
        $pointer = $unsigned = '';
        foreach ($ptypes as $ptype) {
            if ($ptype == self::C_ARRAY) {
                $hasArray = true;
            } else if ($ptype == self::C_INT) {
                $type = self::C_INT;
            } else if ($ptype == Unsigned::class) {
                $unsigned = Unsigned::KEY;
            } elseif (is_subclass_of($ptype, Pointer::class)) {
                $pointer = $ptype::KEY;
            } else {
                $ptype::getTypedef($depsType);
                $type = $ptype::NAME;
            }
        }
        return "$unsigned $type $pointer";
    }
}

class Char extends Type
{
    const NAME = self::C_CHAR;
    const PACK_CODE = 'c';
    const PACK_U_CODE = 'C';
    final const BASE_TYPE = 1;
}

class Short extends Type
{
    const NAME = self::C_SHORT;
    const PACK_CODE = 's';
    const PACK_U_CODE = 'S';
    const PACK_UB_CODE = 'n';
    const PACK_UL_CODE = 'v';
    final const BASE_TYPE = 1;
}
class Int32 extends Type
{
    const NAME = self::C_INT;
    const PACK_CODE = 'l';
    const PACK_U_CODE = 'L';
    const PACK_UB_CODE = 'N';
    const PACK_UL_CODE = 'V';
    final const BASE_TYPE = 1;
}

class Long extends Type
{
    const NAME = self::C_LONG;
    const PACK_CODE = 'q';
    const PACK_U_CODE = 'Q';
    const PACK_UB_CODE = 'J';
    const PACK_UL_CODE = 'P';
    final const BASE_TYPE = 1;
}
class LongDouble extends Type
{
    const NAME = self::C_LONG_DOUBLE;
    final const BASE_TYPE = 1;
}

class CVoid extends Type
{
    const NAME = self::C_VOID;
    final const BASE_TYPE = 1;
}

class Double64 extends Type
{
    const NAME = self::C_DOUBLE;
    const PACK_CODE = 'd';
    const PACK_L_CODE = 'e';
    const PACK_B_CODE = 'E';
    final const BASE_TYPE = 1;
}
class Float32 extends Type
{
    const NAME = self::C_FLOAT;
    const PACK_CODE = 'f';
    const PACK_L_CODE = 'g';
    const PACK_B_CODE = 'G';
    final const BASE_TYPE = 1;
}

abstract class Struct extends Type
{
    const KEY = 'struct';

    public static function getTypedef(array &$depsType = []): void
    {
        $refCls = new \ReflectionClass(static::class);
        if (isset($depsType[static::NAME])) {
            return;
        }
        $depsType[static::NAME] = Type::NAME . ' ' . static::KEY . ' ' . static::NAME . ' ' . static::NAME . ';';
        $code = static::KEY . ' ' . static::NAME . ' { ';
        foreach ($refCls->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic() || !$property->hasType()) {
                continue;
            }
            $hasArray = false;
            $code .= self::getType($property->getType(), $depsType, $hasArray);
            $code .= $property->getName();
            if ($hasArray) {
                $array = $property->getDefaultValue();
                foreach ($array as $i) {
                    $code .= "[$i]";
                }
            }
            $code .= ';' . PHP_EOL;
        }

        foreach ($refCls->getMethods(\ReflectionMethod::IS_ABSTRACT) as $mes) {
            if ($mes->isStatic() || !$mes->hasReturnType() || !$mes->isPublic()) {
                continue;
            }

            $code .= self::getType($mes->getReturnType(), $depsType);
            $code .= '(*' . $mes->name . ')';
            $code .= '(';
            foreach ($mes->getParameters() as $p) {
                $code .= Type::getType($p->getType(), $depsType) . ' ';
                $code .= $p->getName() . ',';
            }
            $code = rtrim($code, ',');
            $code .= ');' . PHP_EOL;
        }
        $code .= '};' . PHP_EOL;
        $depsType[static::NAME] .= $code;
        return;
    }
    public static function getPackCode(?ByteOrder $byteOrder = null): string
    {
        $refCls = new \ReflectionClass(static::class);
        $code = '';
        foreach ($refCls->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if (!$property->isStatic()) {
                $retype = $property->getType();
                if ($retype instanceof \ReflectionNamedType) {
                    $typeClass = $retype->getName();
                    $code .= $typeClass::getPackCode($byteOrder);
                }
            }
        }
        return $code;
    }
}
abstract class Union extends Struct
{
    const KEY = 'union';
}

abstract class CDefine implements CFFI
{
    private static $ffi;
    final public static function load($lib = '')
    {
        $code = self::getCDef();
        echo $code;
        self::$ffi = \FFI::cdef($code, $lib);
    }

    final public static function __callStatic($name, $arguments)
    {
        return self::$ffi->$name(...$arguments);
    }

    final public static function getCDef(): string
    {
        $class = static::class;
        $rc = new \ReflectionClass($class);
        $mes = $rc->getMethods(\ReflectionMethod::IS_ABSTRACT);
        $code = '';
        $depsType = [];
        foreach ($mes as $m) {
            foreach ($m->getAttributes() as $attr) {
                $arg = $attr->getArguments()[0];
                if ($arg instanceof CallingConvention) {
                    $code .= $arg->value . ' ';
                    break;
                }
                continue;
            }

            $code .= Type::getType($m->getReturnType(), $depsType) . ' ';
            $code .= $m->getName() . '(';
            foreach ($m->getParameters() as $p) {
                $code .= Type::getType($p->getType(), $depsType) . ' ';
                $code .= $p->getName() . ',';
            }
            $code = rtrim($code, ',');
            $code .= ');';
        }
        return implode('', $depsType) .  $code;
    }
}
