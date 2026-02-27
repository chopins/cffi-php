<?php

include_once __DIR__ . '/src/V2/Type.php';

class A1 extends \CFFI\Struct
{
    const NAME = 'a1';
    public \CFFI\Int32 $a;
    public \CFFI\Char|array $arr = [3];
}

class tm extends \CFFI\Struct
{
    const NAME = 'tm';
    public \CFFI\Int32 $tm_sec;
    public \CFFI\Int32 $tm_min;
    public \CFFI\Int32 $tm_hour;
    public \CFFI\Int32 $tm_mday;
    public \CFFI\Int32 $tm_mon;
    public \CFFI\Int32 $tm_year;
    public \CFFI\Int32 $tm_wday;
    public \CFFI\Int32 $tm_yday;
    public \CFFI\Int32 $tm_isdst;
    public \CFFI\Int32  $tm_gmtoff;
    public \CFFI\Char|\CFFI\_ $tm_zone;
}

class uintptr_t extends \CFFI\Int32 implements \CFFI\Unsigned {}

abstract class uiControl extends \CFFI\Struct
{
    const NAME = 'uiControl';
    public \CFFI\Int32|\CFFI\Unsigned $Signature;
    public \CFFI\Int32|\CFFI\Unsigned $OSSignature;
    public \CFFI\Int32|\CFFI\Unsigned $TypeSignature;
    abstract public function Destroy(uiControl |\CFFI\_ $a): \CFFI\CVoid;
    abstract public function  Handle(uiControl |\CFFI\_ $a): uintptr_t;
    abstract public function Parent(uiControl|\CFFI\_ $a): uiControl|\CFFI\_;
    abstract public function SetParent(uiControl|\CFFI\_ $a, uiControl|\CFFI\_ $b): CFFI\CVoid;
    abstract public function Toplevel(uiControl|\CFFI\_ $a): int;
    abstract public function Visible(uiControl|\CFFI\_ $a): int;
    abstract public function Show(uiControl|\CFFI\_ $a): \CFFI\CVoid;
    abstract public function Hide(uiControl|\CFFI\_ $a): \CFFI\CVoid;
    abstract public function Enabled(uiControl|\CFFI\_ $a): int;
    abstract public function Enable(uiControl|\CFFI\_ $a): \CFFI\CVoid;
    abstract public function Disable(uiControl|\CFFI\_ $a): \CFFI\CVoid;
};

abstract class test extends \CFFI\CDefine
{
    #[\CFFI\CFFI(\CFFI\CallingConvention::Extern)]
    abstract protected function uiControlParent(uiControl|\CFFI\_ $a): \CFFI\Int32;
}


test::load();

