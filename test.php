<?php

namespace Test;

use CFFI\FFI;
use CFFI\CType\Callback;
use CFFI\CType\Char;
use CFFI\CType\Int32;
use CFFI\CType\Int64;
use CFFI\Struct;
use CFFI\Type;
use CFFI\CType\Unsigned;
use CFFI\CType\CVoid;
use CFFI\CType\_;
use CFFI\CType\Extern;

include_once __DIR__ . '/src/load.php';


class tm extends Struct
{
    private Int32 $tm_sec;
    private Int32 $tm_min;
    private Int32 $tm_hour;
    private Int32 $tm_mday;
    private Int32 $tm_mon;
    private Int32 $tm_year;
    private Int32 $tm_wday;
    private Int32 $tm_yday;
    private Int32 $tm_isdst;
    private Int64 $tm_gmtoff;
    private Char|int $tm_zone = 1;
}

class uiForEach extends Int32 implements Unsigned {}
class size_t extends Int32 implements Unsigned {}
class uint32_t extends Int32 implements Unsigned {}
class uintptr_t extends Int64 implements Unsigned {}

class Destroy extends Callback
{
    public function __invoke(uiControl &$p): void {}
}
class Handle extends Callback
{
    public function __invoke(uiControl &$p): uintptr_t
    {
        return new uintptr_t;
    }
}

class uiControl extends Struct
{
    private uint32_t $Signature;
    private uint32_t $OSSignature;
    private uint32_t $TypeSignature;
    private Destroy $Destroy;
    private Handle $Handle;
    public const DECLARATION_ORDER = 1;
}
class uiInitOptions extends Struct
{
    private size_t $Size;
}

class ChangeCallback extends Callback
{
    public function __invoke(uiWindow &$w,  CVoid&_ $d): void {}
}
class uiWindow extends uiControl
{
    public const DECLARATION_ORDER = 2;
}
class Libui2 extends FFI
{
    #[Extern]
    public function uiControlDestroy(uiControl &$p): void {}
    #[Extern]
    public function &uiAllocControl(size_t $n, uint32_t $OSsig, uint32_t $typesig, Char &$typenamestr): uiControl {
        return new uiControl();
    }
    #[Extern]
    public function uiWindowOnContentSizeChanged(uiWindow &$w, ChangeCallback $f, CVoid&_ $data): void {}
}

$ui = new Libui2('../http/shared/libui.so');
