<?php

namespace Test;

use CFFI\Callback;
use CFFI\Char;
use CFFI\Func;
use CFFI\Int32;
use CFFI\LongInt;
use CFFI\Struct;
use CFFI\Type;
use CFFI\Unsigned;
use CFFI\VoidPointer;

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
    private LongInt $tm_gmtoff;
    private Char|int $tm_zone = 1;
}

class uiForEach extends Int32 implements Unsigned
{
}
class size_t extends Type
{
}
class uint32_t extends Type
{
}
class uintptr_t extends Type
{
}

class Destroy extends Callback
{
    public function Destroy(uiControl &$p): void
    {
    }
}
class Handle extends Callback
{
    public function Handle(uiControl &$p): uintptr_t
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
    public function ChangeCallback(uiWindow &$w, VoidPointer $d): void
    {
    }
}
class uiWindow extends uiControl
{
    public const DECLARATION_ORDER = 2;
}

class Libui  extends Func
{
    private function uiControlDestroy(uiControl &$p): void
    {
    }
    private function &uiAllocControl(size_t $n, uint32_t $OSsig, uint32_t $typesig, Char &$typenamestr): uiControl
    {
        return new uiControl;
    }
    private function uiWindowOnContentSizeChanged(uiWindow &$w, ChangeCallback $f, VoidPointer $data): void
    {
    }
}

$ui = new Libui('../http/shared/libui.so');

