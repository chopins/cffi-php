# The repostiory is only show implements rules for php cffi extension
## Use PHP class declaring C type, struct,union and function

| C type                        | PHP 声明 type                                        | Note                                     |
| ----------------------------- | ---------------------------------------------------- | ---------------------------------------- |
| 类型声明中 `*`                | `C\_`  or `C\Pointer`                                | 1 level Pointer                          |
| 函数参数 `*`                  | `C\_`  or `&`                                        | 1 level Pointer                          |
| `**`                          | `C\__`                                               | 2 level Pointer                          |
| `***`                         | `C\___`                                              | 3 level Pointer                          |
| `****`                        | `C\____`                                             | 4 level Pointer                          |
| 任意指针                      | `#[C\_(LevelNum)]`                                   | `LevelNum` 为层数                        |
| `typedef`                     | `C\Type`                                             | 类型声明继承该类                         |
| `typedef struct`              | `C\Struct`                                           | 结构声明继承该类                         |
| `[]`                          | `C\CArray`                                           |                                          |
| 结构中 `int32_t[10] a`        | `protected Int32\|array $a =[10];`                   | attributes declaration array             |
| `typedef int32_t[10] A`       | `#[C\CArray(10)] class A extends Int32 {}`           | typedef array type                       |
| `typedef int8*(*fn)(int* p)`  | `#[C\Type] function &fn(int &$p):int8 {}`            | 未声明类型时为 `void`                    |
| `void`                        | `class C\CVoid` or `void`                            |                                          |
| `char`                        | `class C\Char`                                       | `char*`为`string`                        |
| `double`                      | `class C\Double`                                     |                                          |
| `float`                       | `class C\Float32` or`float`                          |                                          |
| `long double`                 | `class C\LongDouble`                                 |                                          |
| `int8_t`                      | `class C\Int8`                                       |                                          |
| `int16_t`                     | `class C\Int16`                                      |                                          |
| `int32_t`                     | `class C\Int32`                                      |                                          |
| `int64_t`                     | `class C\Int64`                                      |                                          |
| `signed`                      | `interface C\Signed` or `#[C\signed]`                | use IntersectionType  declaration        |
| `unsigned`                    | `interface C\unsigned`or `#[C\unsigned]`             | use IntersectionType  declaration        |
| `extern`                      | `#[C\Extern]`                                        | attributes                               |
| `__stdcall`                   | `#[C\Stdcall]`                                       | attributes                               |
| `__vectorcall`                | `#[C\Vectorcall]`                                    | attributes                               |
| `__fastcall`                  | `#[C\Fastcall]`                                      | attributes                               |
| 结构中函数`int8（*fn)(int p)` | `abstract protected function fn(int $p):int8`        | attributes                               |
| C函数导入                     | `abstract class C\Import`                            | 用户类继承                               |
| C库加载                       | `C\Import::dl($path)`                                |                                          |
| 导入的C函数定义               | `abstract protected static function fn(int $p):int8` |                                          |
| 导入的C变量                   | `protected static int8 $a`                           | 外部按public访问                         |
| 导入的C enum                  | `const ENUM = []`                                    | 每各枚举列表一个数组，外部可按类常量访问 |

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
use C\Struct;
use C\Union;
use C\Int64;
use C\Unsigned;
use C\Callback;
use C\Int32;
use C\Stdcall;
use C\_;
use C\Type;
use C\Import;
#[Unsigned]
class sigset_t extends Int64 {}
#[Unsigned]
class uint32_t extends Int32 {}
class u extends Union {
    protected uint32_t $type_info;
}

class zend_refcounted_h extends Struct {
    protected uint32_t $refcount;
    protected u $u;
}
class _zend_object extends Struct {
    protected zend_refcounted_h $gc;
    protected uint32_t $handle;
    protected _ & zend_class_entry $ce;
    protected _ & zend_object_handlers $handlers; //use php int value set pointer level, 1 is *, 2 is ** etc
    protected _ & HashTable $properties;
    protected zval|array $properties_table = [1]; // use php array value set array dimensions, like $dimensions of FFI::arrayType
}

#[Type]
function callback_function (Int32 $arg):Int32 {}

#[Import('php.so')]
abstract class PhpCall { //only same namespace below class type/struct be used
    protected static Int32 $globalVar; //extern int globalVar;
    protected static Char|_ $globalVar2; //extern char* globalVar;

    const ENUM = [ 'DAY' => [ 1 => 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']];//enum

    #[Stdcall]
    abstract protected function zend_array_dup(HashTable & __ $source):HashTable & __ ;
    abstract protected function zend_hash_find(HashTable&_ $ht, zend_string & _ $key):zval & __;
}

PhpCall::zend_hash_find();
```