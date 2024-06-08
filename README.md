# The repostiory is only show implements rules for php cffi extension
## Use PHP class declaring C type, struct,union and function

In C define:
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

In PHP define:
```php
namespace TEST;
use CFFI\Struct;
use CFFI\Union;
use CFFI\LongInt;
use CFFI\Unsigned;
use CFFI\Func;
use CFFI\Callback;
use CFFI\Int32;
use CFFI\Stdcall;
class sigset_t extends LongInt implements Unsigned {}
class size_t extends Type //direct extends Type class is c Builtin type
{
}
class unionType extends Union {
    private uint32_t $type_info;
}
class zend_refcounted_h extends Struct {
    private uint32_t $refcount;
    private unionType $u;
}
class _zend_object extends Struct {
    private zend_refcounted_h $gc;
    private uint32_t $handle;
    private int|zend_class_entry $ce = 1;
    private int|zend_object_handlers $handlers = 1; //use php int value set pointer level, 1 is *, 2 is ** etc
    private int|HashTable $properties = 1;
    private array|zval $properties_table = [1]; // use php array value set array dimensions, like $dimensions of FFI::arrayType 
}
class callback_function extends Callback {
    private function callback_function(Int32 $arg):Int32
}
class HashTablePtr extends HashTable implements ReturnPtr {
    public static function ptrLevel() : int {
        return '**';//return 2 level of pointer 
    }
}
class PhpCall extends Func { //only same namespace below class type/struct be used
    private Int32 $globalVar; //extern int globalVar;
    private Char $globalVar2 = 1; //extern char* globalVar;

    const DAY = [ 1 => 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];//enum

    #[Stdcall]
    private function zend_array_dup(HashTable $source = 1):HashTablePtr {
        return new HashTable;
    }
    private function &zend_hash_find(HashTable &$ht, zend_string &$key):zval {
        return new zval;
    }
}
```