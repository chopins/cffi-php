# The repostiory is only show implements rules for php cffi extension
## Use PHP class declaring C type, struct,union and function

1. 基本C类型与PHP类对应关系
   1. `void`: `C\CVoid`
   2. `char`: `C\Char`
   3. `double`: `C\F64`
   4. `float`: `C\F32`
   5. `long double`:`C\FL`
   6. `int8_t`: `C\I8`
   7. `int16_t`:`C\I16`
   8. `int32_t`:`C\I32`
   9. `int64_t`:`C\I64`
2. C修饰符等价PHP类或接口
   1. `typedef`: 抽象类 `C\Type`
   2. `typedef struct`: 抽象类 `C\Struct`
   3. `typedef union`: 抽象类 `C\Union`
   4. `signed`: 接口 `C\Signed`，与基本类型组合
   5. `unsigned`: 接口 `C\Unsigned`，与基本类型组合
   6. `extern`: 接口 `C\Extern`,函数注解,或`C\Import`的子类注解
   7. `__stdcall`: 接口`C\Stdcall`,函数注解,或`C\Import`的子类注解
   8. `__vectorcall`: 接口`C\Vectorcall`,函数注解,或`C\Import`的子类注解
   9. `__fastcall`: 接口`C\Fastcall`,函数注解,或`C\Import`的子类注解
3. 指针
   1. 使用 `C\P` 接口或子接口与基本C类型组合声明指针类型
   2. `C\Pointer::LEVEL` 常量值为指针层数
   3. 定义一个接口继承`C\P`,并定义自定义`LEVEL`以定义任意指针层数
   4. 函数参数中 PHP 引用符`&` 与返回引用表示`*`指针
   5. 结构体中，临时指针类型可以组合 PHP的`int`类型然后定义默认整数值来表示指针层数
   6. 函数中，参数中临时指针类型使用`C\P(LEVEL)`参数注解,`LEVEL`为层数
   7. 函数中，返回临时指针类型使用`C\P(LEVEL)`函数注解，`LEVEL`为层数
4. 数组
   1. 定义数组类型必须实现 `C\CArray` 接口
   2. 类型类中，必须实现`size():array`方法，返回值为一维数组类型，元素值依次表示C数组各维的长度
   3. 结构体中，类型组合php `array`类型，表示数组，属性的值依次表示C数组各维的长度
5. 定义类型，结构，联合体，函数类型
   1. 类型，继承基本类型
   2. 结构继承`C\Struct`, `protected` 属性结构体成员,方法为临时类型的回调函数
   3. 函数类型继承`C\Type`, 原型使用类`__invoke()` 魔术方法
6. 导出的函数
   1. 定义类并继承`C\Import`
   2. 定义类的`DL`常量，值为动态库文件路径
   3. 定义 `protected static` 修饰的类属性与类方法分别表示C全局变量与函数
   4. 定义类的`ENUM`常量，其值为数组，数组中每一个元素定义一组C枚举值

| C type                        | PHP 声明 type                                        | Note                                     |
| ----------------------------- | ---------------------------------------------------- | ---------------------------------------- |
| 类型声明中 `*`                | `C\P`  or `C\Pointer`                                | 1 level Pointer                          |
| 函数参数 `*`                  | `C\P`  or `&`                                        | 1 level Pointer                          |
| `**`                          | `C\P2`                                               | 2 level Pointer                          |
| `***`                         | `C\P3`                                              | 3 level Pointer                          |
| `****`                        | `C\P4`                                             | 4 level Pointer                          |
| 任意指针                      | `#[C\_(Level)]`                                   | `Level` 为层数                        |
| 结构体中 `int32_t ***a`       | `protected Int32\|int $a = 3;`                       | attributes declaration array             |
| 指针类 ``                     | `const LEVEL`                                        | 常量             |
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

abstract class PhpCall { //only same namespace below class type/struct be used
    const DL = 'php.so';
    protected static Int32 $globalVar; //extern int globalVar;
    protected static Char|_ $globalVar2; //extern char* globalVar;

    const ENUM = [ 'DAY' => [ 1 => 'MON', 'TUE', 'WED', 'THU', 'FRI', 'SAT', 'SUN']];//enum

    #[Stdcall]
    abstract protected function zend_array_dup(HashTable & __ $source):HashTable & __ ;
    abstract protected function zend_hash_find(HashTable&_ $ht, zend_string & _ $key):zval & __;
}

PhpCall::zend_hash_find();
```