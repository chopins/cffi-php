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
class HashTablePtr extends HashTable implements ReturnPtr {
    public static function ptrLevel() : int {
        return '**';//return 2 level of pointer
    }
}
class PhpCall extends Func { //only same namespace below class type/struct be used
    private Int32 $globalVar; //extern int globalVar;
    private Char|_ $globalVar2; //extern char* globalVar;

    const DAY = [ 1 => 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN'];//enum

    #[Stdcall]
    private function zend_array_dup(HashTable $source = 1):HashTablePtr & _ {
        return new HashTable;
    }
    private function zend_hash_find(HashTable&_ $ht, zend_string & _ $key):zval & __ {
        return new zval;
    }
}
```