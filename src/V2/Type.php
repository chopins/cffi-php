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
    const C_LONG_LONG = 'long long';
    const C_DOUBLE = 'double';
    const C_FLOAT = 'float';
    const C_LONG_DOUBLE = 'long double';
    const C_VOID = 'void';
    const C_ARRAY = 'array';
    private static $defaultByteOrder = ByteOrder::HOST_BYTE_ORDER;
    private static string $ffiClass = '';

    public static function new($value = null, bool $owned = true, bool $persistent = false)
    {
        $cdata = self::getFFI()->new(self::type(), $owned, $persistent);
        if ($value !== null) {
            self::setValue($cdata, $value);
        }
        return $cdata;
    }

    public static function getFFI(): \FFI
    {
        if (!self::$ffiClass) {
            return \FFI::cdef();
        } else {
            return CDefine::getLibFFI(self::$ffiClass);
        }
    }

    public static function newArray(array $value, bool $owned = true, bool  $persistent = false)
    {
        $e = $value;
        $dim = [];
        do {
            $dim[] = count($e);
            $e = $e[0];
        } while (is_array($e));

        $type = \FFI::arrayType(self::type(), [array_product($dim)]);
        $cdata = self::getFFI()->new($type, $owned, $persistent);
        array_walk_recursive($value, function ($value) use (&$cdata) {
            $cdata[] = $value;
        });
        return \FFI::cast(\FFI::arrayType(self::type(), $dim), $cdata);
    }

    public static function cast(\FFI\CData|int|float|bool|null|CFFI $ptr): \FFI\CData
    {
        return \FFI::cast(self::type(), $ptr);
    }
    public static function castptr(\FFI\CData|int|float|bool|null|CFFI $ptr): \FFI\CData
    {
        return \FFI::cast(self::ptrType(), $ptr);
    }
    public static function ptrType()
    {
        return self::getFFI()->type(static::NAME . '*');
    }
    public static function type()
    {
        return self::getFFI()->type(static::NAME);
    }

    public static function setValue(\FFI\CData $cdata, $value)
    {
        $cdata->cdata = $value;
    }

    public static function addr(\FFI\CData $cdata)
    {
        return \FFI::addr($cdata);
    }

    public static function free(\FFI\CData $cdata)
    {
        return \FFI::free($cdata);
    }

    public static function typeof(\FFI\CData $cdata)
    {
        return \FFI::typeof($cdata);
    }

    public static function memset(\FFI\CData $cdata, int $value, int $size)
    {
        return \FFI::memset($cdata, $value, $size);
    }

    public static function string(\FFI\CData $cdata)
    {
        return \FFI::string($cdata);
    }

    public static function sizeof()
    {
        return \FFI::sizeof(self::type());
    }

    public static function isNull(\FFI\CData $cdata)
    {
        return \FFI::isNull($cdata);
    }

    public static function getTypedef(array &$depsType = [], $ffiClass): void
    {
        if (static::BASE_TYPE) {
            return;
        }
        $pclass = get_parent_class(static::class);
        if ($pclass == self::class) { //callback function
            $invoke = new \ReflectionMethod(static::class, '__invoke');
            $depsType[static::NAME] = self::NAME . ' ' . self::getFunctionDef($invoke, static::NAME, $depsType, $ffiClass);
            return;
        }
        //base type
        $unsigned = static::class instanceof Unsigned ? Unsigned::KEY : '';
        $depsType[static::NAME] = self::NAME . " $unsigned " . $pclass::NAME . ' ' . static::NAME . PHP_EOL;
        return;
    }

    public static function isMinPHPVersion(\ReflectionMethod|\ReflectionProperty $member)
    {
        $attr = $member->getAttributes(MinPHPVersion::class);
        if ($attr) {
            $arg = $attr[0]->getArguments();
            if (PHP_VERSION_ID < $arg[0]) {
                return false;
            }
        }
        return true;
    }

    public static function getFunctionDef(\ReflectionMethod $m, $name, &$depsType, $ffiClass)
    {
        $hasArray = false;
        $code = Type::getType($m->getReturnType(), $depsType, $hasArray, $ffiClass) . ' ';
        $code .= $name . '(';
        foreach ($m->getParameters() as $p) {
            $hasArray = false;
            $code .= Type::getType($p->getType(), $depsType, $hasArray, $ffiClass) . ' ';
            $code .= $p->getName() . ',';
        }
        $code = rtrim($code, ',');
        $code .= ');';
        return $code;
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

    final public static function getType(\ReflectionType $type, array &$depsType = [], bool &$hasArray = false, $ffiClass): string
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
                $ptype::$ffiClass = $ffiClass;
                $ptype::getTypedef($depsType, $ffiClass);
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
class LongLong extends Type
{
    const NAME = self::C_LONG_LONG;
    final const BASE_TYPE  = 1;
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
    public static function setValue(\FFI\CData $cdata, $value)
    {
        foreach ($value as $feild => $v) {
            $cdata->$feild = $v;
        }
    }
    public static function getTypedef(array &$depsType = [], $ffiClass = ''): void
    {
        $refCls = new \ReflectionClass(static::class);
        if (isset($depsType[static::NAME])) {
            return;
        }
        $className = $refCls->getShortName();
        $depsType[static::NAME] = Type::NAME . ' ' . static::KEY . " _$className " . static::NAME . ';';
        $code = static::KEY . " _$className { ";
        foreach ($refCls->getProperties(\ReflectionProperty::IS_PUBLIC) as $property) {
            if ($property->isStatic() || !$property->hasType()) {
                continue;
            }
            if(!self::isMinPHPVersion($property)) {
                continue;
            }
            $hasArray = false;
            $code .= self::getType($property->getType(), $depsType, $hasArray, $ffiClass);
            $code .= $property->getName();
            if ($hasArray) {
                $array = $property->getDefaultValue();
                foreach ($array as $i) {
                    $code .= "[$i]";
                }
            }
            $code .= ';';
        }

        foreach ($refCls->getMethods(\ReflectionMethod::IS_ABSTRACT) as $mes) {
            if ($mes->isStatic() || !$mes->hasReturnType() || !$mes->isPublic()) {
                continue;
            }
            if(!self::isMinPHPVersion($mes)) {
                continue;
            }
            $code .= self::getFunctionDef($mes, '(*' . $mes->name . ')', $depsType, $ffiClass);
        }
        $code .= '};';
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

class zval extends Struct
{
    const NAME = 'zval';
    public CVoid|_ $res;
    public Int32|Unsigned $type_info;
    public Int32|Unsigned $num_args;
}
class zend_execute_data extends Struct
{
    const NAME = 'zend_execute_data';
    public CVoid|_ $opline;
    public zend_execute_data|_ $call;
    public zval|_ $return_value;
    public CVoid|_ $func;
    public zval $This;
    public   zend_execute_data|_ $prev_execute_data;
    public  CVoid|_ $symbol_table;
    public  CVoid|__ $run_time_cache;
    public  CVoid|_ $extra_named_params;
}
if (PHP_INT_SIZE == 8) {
    class size_t extends LongLong implements Unsigned {}
} else {
    class size_t extends Int32 implements Unsigned {}
}
final class MinPHPVersion {}

class zend_executor_globals extends Struct
{
    const NAME = 'zend_executor_globals';
    const __SYMTABLE_CACHE_SIZE__ = 32;
    const __ZEND_ARRAY_SIZE__ = 48 + \PHP_INT_SIZE;
    public zval $uninitialized_zval;
    public zval $error_zval;
    public CVoid|array|_ $symtable_cache = [self::__SYMTABLE_CACHE_SIZE__];
    public CVoid|__ $symtable_cache_limit;
    public CVoid|__ $symtable_cache_ptr;
    public Char|array $symbol_table = [self::__ZEND_ARRAY_SIZE__];
    public Char|array $included_files = [self::__ZEND_ARRAY_SIZE__];
    public CVoid|_ $bailout;
    public int $error_reporting;
    #[MinPHPVersion(80500)]
    public bool $fatal_error_backtrace_on;
    #[MinPHPVersion(80500)]
    public zval $last_fatal_error_backtrace;

    public int $exit_status;
    public CVoid|_ $function_table;
    public CVoid|_ $class_table;
    public CVoid|_ $zend_constants;
    public zval|_ $vm_stack_top;
    public zval|_ $vm_stack_end;
    public CVoid|_ $vm_stack; //zend_vm_stack, typedef struct _zend_vm_stack *zend_vm_stack;
    public size_t $vm_stack_page_size;
    public CVoid| _ $current_execute_data;
    public CVoid| _ $fake_scope;
    /* Other member fields are omitted .... */
    /* ....... */
}
abstract class PHPNTSAPI extends CDefine
{
    protected static zend_executor_globals $executor_globals;
}
abstract class PHPZTSAPI extends CDefine
{
    protected static size_t $executor_globals_offset;
    abstract protected static function tsrm_get_ls_cache(): CVoid|_;
}

abstract class CDefine implements CFFI
{
    private static array $ffi;
    const ENUM = [];
    final public static function load($lib = '')
    {
        $calledClass = static::class;
        if (isset(self::$ffi[$calledClass])) {
            return self::$ffi[$calledClass];
        }
        $code = self::enum() . self::getCDef();
        self::$ffi[$calledClass] = \FFI::cdef($code, $lib);
        return self::$ffi[$calledClass];
    }

    final public static function getLibFFI($class): \FFI
    {
        return self::$ffi[$class];
    }

    final public static function __callStatic($name, $arguments)
    {
        return self::$ffi[static::class]->$name(...$arguments);
    }

    final public static function getZval($var)
    {
        if (\PHP_ZTS) {
            PHPZTSAPI::load();
            $tsrm = Char::castptr(PHPZTSAPI::tsrm_get_ls_cache());
            $cex = zend_executor_globals::castptr($tsrm + PHPZTSAPI::$executor_globals_offset->cdata)->current_execute_data;
        } else {
            PHPNTSAPI::load();
            $cex = PHPNTSAPI::$executor_globals->current_execute_data;
        }
        $ex = zval::castptr($cex);
        $zvalSize = zval::sizeof();
        $exSize = zend_execute_data::sizeof();
        $arg = $ex + (($exSize + $zvalSize - 1) / $zvalSize);
        return zval::cast($arg);
    }

    final public static function enum(): string
    {
        $enumCDef = '';
        foreach (static::ENUM as $enum) {
            $enumCDef .= 'typedef enum {';
            foreach ($enum as $k => $v) {
                if (is_string($v) && is_int($k)) {
                    $enumCDef .= "$v,";
                } else if (is_string($k) && is_int($v)) {
                    $enumCDef .= "$k = $v,";
                }
            }
            $enumCDef = rtrim($enumCDef, ',');
            $enumCDef .= "};";
        }
        return $enumCDef;
    }

    final public static function getCDef(): string
    {
        $class = static::class;
        $rc = new \ReflectionClass($class);
        $mes = $rc->getMethods(\ReflectionMethod::IS_ABSTRACT);
        $code = '';
        $depsType = [];
        foreach ($mes as $m) {
            if(!self::isMinPHPVersion($m)) {
                continue;
            }
            foreach ($m->getAttributes() as $attr) {
                $arg = $attr->getArguments()[0];
                if ($arg instanceof CallingConvention) {
                    $code .= $arg->value . ' ';
                    break;
                }
                continue;
            }
            $code .= Type::getFunctionDef($m, $m->getName(), $depsType, $class);
        }
        $proes = $rc->getProperties(\ReflectionProperty::IS_PROTECTED);
        foreach ($proes as $pro) {
            if(!self::isMinPHPVersion($pro)) {
                continue;
            }
            $hasArray = false;
            $code .= Type::getType($pro->getType(), $depsType, $hasArray, $class);
            $code .= $pro->getName();
            if ($hasArray) {
                $dim = $pro->getDefaultValue();
                foreach ($dim as $i) {
                    $code .= "[$i]";
                }
            }
            $code = ';';
        }
        return implode('', $depsType) .  $code;
    }
}
