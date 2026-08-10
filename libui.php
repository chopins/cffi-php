<?php

include_once __DIR__ . '/src/V2/Type.php';

use C\Type;
use C\Struct;
use C\Char;
use C\Long;
use C\Int32;
use C\_;
use C\Unsigned;
use C\CDefine;
use C\CVoid;

class tm extends Struct
{
    const NAME = 'tm';
    private int $tm_sec;
    private int $tm_min;
    private int $tm_hour;
    private int $tm_mday;
    private int $tm_mon;
    private int $tm_year;
    private int $tm_wday;
    private int $tm_yday;
    private int $tm_isdst;
    private Long $tm_gmtoff;
    private Char|_ $tm_zone;
};

class uiForEach extends Int32 implements Unsigned
{
    const NAME = 'uiForEach';
}
class Uint32t extends Int32 implements Unsigned
{
    const NAME = 'uint32_t';
}
class SizeT extends Long implements Unsigned
{
    const NAME = 'size_t';
}
class uintptr_t extends Long implements Unsigned
{
    const NAME = 'uintptr_t';
}
class uiInitOptions extends Struct
{
    const NAME = 'uiInitOptions';
    protected SizeT $Size;
}

class uiControl extends Struct
{
    const NAME = 'uiControl';
    protected Uint32t $Signature;
    protected Uint32t $OSSignature;
    protected Uint32t $TypeSignature;
    protected function Destroy(uiControl|_ $a): void {}
    protected function Handle(uiControl|_ $a): ?uintptr_t
    {
        return NULL;
    }
    protected function Parent(uiControl &$a): uiControl|_|NULL
    {
        return NULL;
    }
    protected function SetParent(uiControl|_ $a, uiControl|_ $b): void {}
    protected function Toplevel(uiControl|_ $a): ?int
    {
        return NULL;
    }
    protected function Visible(uiControl|_ $a): ?int
    {
        return NULL;
    }
    protected function Show(uiControl|_ $a): void {}
    protected function Hide(uiControl|_ $a): ?int
    {
        return NULL;
    }
    protected function Enable(uiControl|_ $a): void {}
    protected function Disable(uiControl|_ $a): void {}
};

class F1 extends Type
{
    const NAME = 'f1';
    public function __invoke(CVoid|_ $data): void {}
}

#[Type()]
function F1():?\Toknot\Type\int16 {
    return null;
}

class F2 extends Type
{
    const NAME = 'f2';
    public function __invoke(CVoid|_ $data): ?int
    {
        return NULL;
    }
}

class uiWindow extends uiControl
{
    const NAME = 'uiWindow';
}

class uiButton extends uiControl
{
    const NAME = 'uiButton';
}

class uiBox extends uiControl
{
    const NAME = 'uiBox';
}
class uiCheckbox extends uiControl
{
    const NAME = 'uiCheckbox';
}
class uiEntry extends uiControl
{
    const NAME = 'uiEntry';
}
class uiLabel extends uiControl
{
    const NAME = 'uiLabel';
}
class uiTab extends uiControl
{
    const NAME = 'uiTab';
}
class uiGroup extends uiControl
{
    const NAME = 'uiGroup';
}
class uiSpinbox extends uiControl
{
    const NAME = 'uiSpinbox';
}

class uiProgressBar extends uiControl
{
    const NAME = 'uiProgressBar';
}

class uiSlider extends uiControl
{
    const NAME = 'uiSlider';
}

class uiSeparator extends uiControl
{
    const NAME = 'uiSeparator';
}

class uiCombobox extends uiControl
{
    const NAME = 'uiCombobox';
}
class uiEditableCombobox extends uiControl
{
    const NAME = 'uiEditableCombobox';
}
class uiRadioButtons extends uiControl
{
    const NAME = 'uiRadioButtons';
}
class uiDateTimePicker extends uiControl
{
    const NAME = 'uiDateTimePicker';
}
class uiMultilineEntry extends uiControl
{
    const NAME = 'uiMultilineEntry';
}
class uiArea extends uiControl
{
    const NAME = 'uiArea';
}
class uiMenuItem extends uiControl
{
    const NAME = 'uiMenuItem';
}
class uiMenu extends uiControl
{
    const NAME = 'uiMenu';
}


class libui extends CDefine
{
    const ENUM = [
        [
            'uiForEachContinue',
            'uiForEachStop'
        ],
        [
            'uiWindowResizeEdgeLeft',
            'uiWindowResizeEdgeTop',
            'uiWindowResizeEdgeRight',
            'uiWindowResizeEdgeBottom',
            'uiWindowResizeEdgeTopLeft',
            'uiWindowResizeEdgeTopRight',
            'uiWindowResizeEdgeBottomLeft',
            'uiWindowResizeEdgeBottomRight'
        ],
        [
            'uiDrawBrushTypeSolid',
            'uiDrawBrushTypeLinearGradient',
            'uiDrawBrushTypeRadialGradient',
            'uiDrawBrushTypeImage'
        ],
        [
            'uiDrawLineCapFlat',
            'uiDrawLineCapRound',
            'uiDrawLineCapSquare'
        ],
        [
            'uiDrawLineJoinMiter',
            'uiDrawLineJoinRound',
            'uiDrawLineJoinBevel'
        ],
        [
            'uiDrawFillModeWinding',
            'uiDrawFillModeAlternate'
        ],
        [
            'uiAttributeTypeFamily',
            'uiAttributeTypeSize',
            'uiAttributeTypeWeight',
            'uiAttributeTypeItalic',
            'uiAttributeTypeStretch',
            'uiAttributeTypeColor',
            'uiAttributeTypeBackground',
            'uiAttributeTypeUnderline',
            'uiAttributeTypeUnderlineColor',
            'uiAttributeTypeFeatures'
        ],
        [
            'uiTextWeightMinimum' => 0,
            'uiTextWeightThin' => 100,
            'uiTextWeightUltraLight' => 200,
            'uiTextWeightLight' => 300,
            'uiTextWeightBook' => 350,
            'uiTextWeightNormal' => 400,
            'uiTextWeightMedium' => 500,
            'uiTextWeightSemiBold' => 600,
            'uiTextWeightBold' => 700,
            'uiTextWeightUltraBold' => 800,
            'uiTextWeightHeavy' => 900,
            'uiTextWeightUltraHeavy' => 950,
            'uiTextWeightMaximum' => 1000
        ],
        [
            'uiTextItalicNormal',
            'uiTextItalicOblique',
            'uiTextItalicItalic'
        ],
        [
            'uiTextStretchUltraCondensed',
            'uiTextStretchExtraCondensed',
            'uiTextStretchCondensed',
            'uiTextStretchSemiCondensed',
            'uiTextStretchNormal',
            'uiTextStretchSemiExpanded',
            'uiTextStretchExpanded',
            'uiTextStretchExtraExpanded',
            'uiTextStretchUltraExpanded'
        ],
        [
            'uiUnderlineNone',
            'uiUnderlineSingle',
            'uiUnderlineDouble',
            'uiUnderlineSuggestion'
        ],
        [
            'uiUnderlineColorCustom',
            'uiUnderlineColorSpelling',
            'uiUnderlineColorGrammar',
            'uiUnderlineColorAuxiliary'
        ],
        [
            'uiModifierCtrl' => 1 << 0,
            'uiModifierAlt' => 1 << 1,
            'uiModifierShift' => 1 << 2,
            'uiModifierSuper' => 1 << 3
        ],
        [
            'uiExtKeyEscape' => 1,
            'uiExtKeyInsert',
            'uiExtKeyDelete',
            'uiExtKeyHome',
            'uiExtKeyEnd',
            'uiExtKeyPageUp',
            'uiExtKeyPageDown',
            'uiExtKeyUp',
            'uiExtKeyDown',
            'uiExtKeyLeft',
            'uiExtKeyRight',
            'uiExtKeyF1',
            'uiExtKeyF2',
            'uiExtKeyF3',
            'uiExtKeyF4',
            'uiExtKeyF5',
            'uiExtKeyF6',
            'uiExtKeyF7',
            'uiExtKeyF8',
            'uiExtKeyF9',
            'uiExtKeyF10',
            'uiExtKeyF11',
            'uiExtKeyF12',
            'uiExtKeyN0',
            'uiExtKeyN1',
            'uiExtKeyN2',
            'uiExtKeyN3',
            'uiExtKeyN4',
            'uiExtKeyN5',
            'uiExtKeyN6',
            'uiExtKeyN7',
            'uiExtKeyN8',
            'uiExtKeyN9',
            'uiExtKeyNDot',
            'uiExtKeyNEnter',
            'uiExtKeyNAdd',
            'uiExtKeyNSubtract',
            'uiExtKeyNMultiply',
            'uiExtKeyNDivide'
        ],
        [
            'uiAlignFill',
            'uiAlignStart',
            'uiAlignCenter',
            'uiAlignEnd'
        ],
        [
            'uiAtLeading',
            'uiAtTop',
            'uiAtTrailing',
            'uiAtBottom'
        ],
        [
            'uiTableValueTypeString',
            'uiTableValueTypeImage',
            'uiTableValueTypeInt',
            'uiTableValueTypeColor'
        ]
    ];

    protected function uiInit(uiInitOptions|_ $options): Char|_|NULL
    {
        return null;
    }
    protected function  uiUninit(): void {}
    protected function uiFreeInitError(Char|_ $err): void {}
    protected function uiMain(): void {}
    protected function uiMainSteps(): void {}
    protected function uiMainStep(int $wait): ?int
    {
        return NULL;
    }
    protected function uiQuit(): void {}
    protected function uiQueueMain(F1 $f, CVoid|_ $data): void {}
    protected function uiTimer(int $milliseconds, F2 $f, CVoid|_ $data): void {}
    protected function uiOnShouldQuit(F2 $f, CVoid|_ $data): void {} //int (*f)(void *data)
    protected function uiFreeText(Char|_ $text): void {}
    protected function   uiControlDestroy(uiControl|_ $a): void {}
    protected function   uiControlHandle(uiControl|_ $a): ?uintptr_t
    {
        return NULL;
    }
    protected function  uiControlParent(uiControl|_ $a): uiControl|_|NULL
    {
        return NULL;
    }
    protected function   uiControlSetParent(uiControl|_ $a, uiControl|_ $b): void {}
    protected function   uiControlToplevel(uiControl|_ $a): ?int
    {
        return NULL;
    }
    protected function   uiControlVisible(uiControl|_ $a): ?int
    {
        return NULL;
    }
    protected function   uiControlShow(uiControl|_ $a): void {}
    protected function   uiControlHide(uiControl|_ $a): void {}
    protected function   uiControlEnabled(uiControl|_ $a): ?int
    {
        return NULL;
    }
    protected function   uiControlEnable(uiControl|_ $a): void {}
    protected function   uiControlDisable(uiControl|_ $a): void {}
    protected function uiNewWindow(Char|_ $title, int $width, int $height, int $hasMenubar): uiWindow|_|NULL
    {
        return NULL;
    }
    protected function uiWindowOnClosing(uiWindow|_ $w, F2 $f, CVoid|_ $data): void {}

    public static function uiControl($w)
    {
        return uiControl::castptr($w);
    }
}

class Str extends Char implements _ {
    const NAME = 'char*';
}

$uiffi = new libui(dirname(__DIR__) . '/http/shared/libui.so');

$op = new uiInitOptions;
$optr  = $op->addr();
$optr->memset(0, uiInitOptions::sizeof());
$uiffi->uiInit($op->addr());
$w = $uiffi->uiNewWindow(new Str("Hello", false), 320, 240, 0);

$uiffi->uiControlShow(libui::uiControl($w));
$uiffi->uiMain();
function A() {}
