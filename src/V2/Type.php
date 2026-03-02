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
    private static $defaultByteOrder = ByteOrder::HOST_BYTE_ORDER;
    public static ?\FFI $cffi = null;

    public static function new($value = null, bool $owned = true, bool $persistent = false)
    {
        if (!self::$cffi) {
            $ffi = \FFI::cdef();
        } else {
            $ffi = self::$cffi;
        }
        $cdata = $ffi->new(self::type(), $owned, $persistent);
        if ($value !== null) {
            self::setValue($cdata, $value);
        }
        return $cdata;
    }

    public static function newArray(array $value, bool $owned = true, bool  $persistent = false)
    {
        if (!self::$cffi) {
            $ffi = \FFI::cdef();
        } else {
            $ffi = self::$cffi;
        }
        $e = $value;
        $dim = [];
        do {
            $dim[] = count($e);
            $e = $e[0];
        } while (is_array($e));

        $type = \FFI::arrayType(self::type(), [array_product($dim)]);
        $cdata = $ffi->new($type, $owned, $persistent);
        array_walk_recursive($value, function ($value) use (&$cdata) {
            $cdata[] = $value;
        });
        return \FFI::cast(\FFI::arrayType(self::type(), $dim), $cdata);
    }

    public static function cast(\FFI\CData|int|float|bool|null $ptr): \FFI\CData
    {
        return \FFI::cast(self::type(), $ptr);
    }

    public static function type()
    {
        if (self::$cffi) {
            return self::$cffi->type(static::NAME);
        } else {
            return \FFI::cdef()->type(static::NAME);
        }
    }

    public static function setValue(\FFI\CData $cdata, $value)
    {
        $cdata->cdata = $value;
    }

    public static function setFFI(\FFI $cffi)
    {
        self::$cffi = $cffi;
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

    public static function getTypedef(array &$depsType = []): void
    {
        if (static::BASE_TYPE) {
            return;
        }
        $pclass = get_parent_class(static::class);
        if ($pclass == self::class) {
            $invoke = new \ReflectionMethod(static::class, '__invoke');
            $depsType[static::NAME] = self::NAME . ' ' . self::getFunctionDef($invoke, static::NAME, $depsType);
            return;
        }
        $unsigned = static::class instanceof Unsigned ? Unsigned::KEY : '';
        $depsType[static::NAME] = self::NAME . " $unsigned " . $pclass::NAME . ' ' . static::NAME . PHP_EOL;
        return;
    }

    public static function getFunctionDef(\ReflectionMethod $m, $name, &$depsType)
    {
        $code = Type::getType($m->getReturnType(), $depsType) . ' ';
        $code .= $name . '(';
        foreach ($m->getParameters() as $p) {
            $code .= Type::getType($p->getType(), $depsType) . ' ';
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
    public static function setValue(\FFI\CData $cdata, $value)
    {
        foreach ($value as $feild => $v) {
            $cdata->$feild = $v;
        }
    }
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
            $code .= ';';
        }

        foreach ($refCls->getMethods(\ReflectionMethod::IS_ABSTRACT) as $mes) {
            if ($mes->isStatic() || !$mes->hasReturnType() || !$mes->isPublic()) {
                continue;
            }
            $code .= self::getFunctionDef($mes, '(*' . $mes->name . ')', $depsType);
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

abstract class CDefine implements CFFI
{
    private static \FFI $ffi;
    const ENUM = [];
    final public static function load($lib = '')
    {
        $code = self::enum() . self::getCDef();
        self::$ffi = \FFI::cdef($code, $lib);
        return self::$ffi;
    }

    final public static function __callStatic($name, $arguments)
    {
        return self::$ffi->$name(...$arguments);
    }

    final public static function getZval($var)
    {
        $code = 'typedef struct{void *res;uint32_t type_info;uint32_t num_args;} zval;
        typedef struct _zend_execute_data zend_execute_data;
        struct _zend_execute_data {
            const void *opline;
            zend_execute_data *call;
            zval *return_value;
            void *func;
            zval This;
            zend_execute_data *prev_execute_data;
            void *symbol_table;
            void **run_time_cache;
            void *extra_named_params;
        };
        typedef struct {
            zval uninitialized_zval;
            zval error_zval;
            void *symtable_cache[__SYMTABLE_CACHE_SIZE__];
            void **symtable_cache_limit;
            void **symtable_cache_ptr;
            char symbol_table[__ZEND_ARRAY_SIZE__];
            char included_files[__ZEND_ARRAY_SIZE__];
            void *bailout;
            int error_reporting;
            __PHP85_EG_FEILDS__
            int exit_status;
            void *function_table;
            void *class_table;
            void *zend_constants;
            zval *vm_stack_top;
            zval *vm_stack_end;
            void* vm_stack;//zend_vm_stack, typedef struct _zend_vm_stack *zend_vm_stack;
            size_t vm_stack_page_size;
            void *current_execute_data;
            void *fake_scope;
            /* Other member fields are omitted .... */
            /* ....... */
        } zend_executor_globals;';
        $code = \str_replace(
            ['__SYMTABLE_CACHE_SIZE__', '__ZEND_ARRAY_SIZE__', 'zend_long', '__PHP85_EG_FEILDS__'],
            [
                32,
                48 + \PHP_INT_SIZE,
                \PHP_INT_SIZE == 8 ? 'int64_t' : 'int32_t',
                \PHP_VERSION_ID >= 80500 ? 'bool fatal_error_backtrace_on;zval last_fatal_error_backtrace;' : '',
            ],
            $code
        );
        if (\PHP_ZTS) {
            $code .= 'void *tsrm_get_ls_cache(void);size_t executor_globals_offset;';
        } else {
            $code .= 'zend_executor_globals executor_globals;';
        }
        $ffi = \FFI::cdef($code);
        if (\PHP_ZTS) {
            $tsrm = $ffi->cast('char*', $ffi->tsrm_get_ls_cache());
            $cex = $ffi->cast('zend_executor_globals*', $tsrm + $ffi->executor_globals_offset)->current_execute_data;
        } else {
            $cex = $ffi->executor_globals->current_execute_data;
        }
        $ex = $ffi->cast('zval*', $cex);
        $zvalSize = FFI::sizeof($ffi->type('zval'));
        $exSize = FFI::sizeof($ffi->type('zend_execute_data'));
        $arg = $ex + (($exSize + $zvalSize - 1) / $zvalSize);
        return $ffi->cast('zval', $arg);
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
            foreach ($m->getAttributes() as $attr) {
                $arg = $attr->getArguments()[0];
                if ($arg instanceof CallingConvention) {
                    $code .= $arg->value . ' ';
                    break;
                }
                continue;
            }
            $code .= Type::getFunctionDef($m, $m->getName(), $depsType);
        }
        return implode('', $depsType) .  $code;
    }
}
