## 基本类
* CFFI  基本类
    ```php
    namespace CFFI;
    abstract class FFI {}
    ```
* Type  基本类型
    ```php
    namespace CFFI;
    abstract class Type extends FFI {}
    class Char extends Type {}
    class Int32 extends Type {}
    class Float extends Type {}
    class Double extends Type {}
    class Void extends Type {}
    class Int64 extends Type {}
    ```
* Struct 结构体
    ```php
    abstract class Struct extends Type {}
    ```
* Union 共用体
    ```php
    abstract class Union extends Type {}
    ```
* Enum 枚举
* unsigned
    ```php
    interface unsigned {}
    ```
* signed
    ```php
    interface signed {}
    ```
* long
    ```php
    interface long {}
    ```
* short
    ```php
    interface short {}
    ```
## 实例
```php
class Foo extends Struct {
    public Int32 $a;
    public array|Char $b = [4];// char[4] b
    public int|Char $c = 1; // char* c
    public int|Char $d = 2; //char** d

    public function cfunction(int|Char $a = 3)// cfunction(char ***a)
    {

    }
}
```
