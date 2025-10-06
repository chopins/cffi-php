# The repostiory is only show implements rules for php cffi extension
## Use PHP class declaring C type, struct,union and function

| C type                  | PHP type                                                    | Note                                   |
| ----------------------- | ----------------------------------------------------------- | -------------------------------------- |
| `*`                     | `interface CFFI\CType\_`  or `interface CFFI\CType\Pointer` | 1 level Pointer, use Intersection Type |
| `**`                    | `interface CFFI\CType\__`                                   | 2 level Pointer                        |
| `***`                   | `interface CFFI\CType\___`                                  | 3 level Pointer                        |
| `****`                  | `interface CFFI\CType\____`                                 | 4 level Pointer                        |
| `void *(*funcName)()`   | `funcName::__invoke():CVoid&_`                              | class __invoke method                  |
| `void`                  | `class CFFI\CTypeCVoid`                                     |                                        |
| `typedef int32_t[10] A` | `class A extends Int32 { const SIZE = 10;}`                 | typedef array type                     |
| `int32_t[10] a`         | `#[CFFI\CType\CArray(10)] Int32 $a`                         | attributes declaration array           |
| `char`                  | `class CFFI\CType\Char`                                     |                                        |
| `double`                | `class CFFI\CType\Double64`                                 |                                        |
| `float`                 | `class CFFI\CType\Float32`                                  |                                        |
| `long double`           | `class CFFI\CType\LongDouble`                               |                                        |
| `int8_t`                | `class CFFI\CType\Int8`                                     |                                        |
| `int16_t`               | `class CFFI\CType\Int16`                                    |                                        |
| `int32_t`               | `class CFFI\CType\Int32`                                    |                                        |
| `int64_t`               | `class CFFI\CType\Int64`                                    |                                        |
| `signed`                | `interface CFFI\CType\Signed`                               | use IntersectionType  declaration      |
| `unsigned`              | `interface CFFI\CType\unsigned`                             | use IntersectionType  declaration      |
| `extern`                | `#[CFFI\CType\Extern]`                                      | attributes                             |
| `__stdcall`             | `#[CFFI\CType\Stdcall]`                                     | attributes                             |
| `__vectorcall`          | `#[CFFI\CType\Vectorcall]`                                  | attributes                             |
| `__fastcall`            | `#[CFFI\CType\Fastcall]`                                    | attributes                             |


##### In C define:
```c
typedef unsigned long sigset_t;
typedef struct _zend_refcounted_h {
    uint32_t         refcount;                      /* reference counter 32-bit */
    union {
            uint32_t type_info;
    } u;
} zend_refcounted_h;

struct _zend_object {
    zend_refcounted_h gc;
    uint32_t          handle; // TODO: may be removed ???
    zend_class_entry *ce;
    const zend_object_handlers *handlers;
    HashTable        *properties;
    zval              properties_table[1];
};
int callback_function(int arg);
__stdcall HashTable**  zend_array_dup(HashTable **source);
zval* zend_hash_find(const HashTable *ht, zend_string *key);
```

##### In PHP define:
```php
namespace TEST;
use CFFI\Struct;
use CFFI\Union;
use CFFI\CType\Int64;
use CFFI\CType\Unsigned;
use CFFI\CType\Callback;
use CFFI\CType\Int32;
use CFFI\CType\Stdcall;
use CFFI\CType\_;
class sigset_t extends Int64 implements Unsigned {}
class uint32_t extends Int32 implements Unsigned {}
class u extends Union {
    private uint32_t $type_info;
}

class zend_refcounted_h extends Struct {
    private uint32_t $refcount;
    private u $u;
}
class _zend_object extends Struct {
    private zend_refcounted_h $gc;
    private uint32_t $handle;
    private _ & zend_class_entry $ce;
    private _ & zend_object_handlers $handlers; //use php int value set pointer level, 1 is *, 2 is ** etc
    private _ & HashTable $properties;
    #[CArray(1)]
    private zval $properties_table; // use php array value set array dimensions, like $dimensions of FFI::arrayType
}
class callback_function extends Callback {
    public function __invoke(Int32 $arg):Int32 {
    }
}

class PhpCall extends Func { //only same namespace below class type/struct be used
    private Int32 $globalVar; //extern int globalVar;
    private Char|_ $globalVar2; //extern char* globalVar;

    const DAY = [ 1 => 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];//enum

    #[Stdcall]
    private function zend_array_dup(HashTable & __ $source):HashTable & __ {
        return new HashTable;
    }
    private function zend_hash_find(HashTable&_ $ht, zend_string & _ $key):zval & __ {
        return new zval;
    }
}
$lib = new PhpCall;
$lib->zend_hash_find();
```