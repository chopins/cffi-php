<?php

/**
 * cffi-php (http://toknot.com)
 *
 * @copyright  Copyright (c) 2019 Szopen Xiao (Toknot.com)
 * @license    http://toknot.com/LICENSE.txt New BSD License
 * @link       https://github.com/chopins/cffi-php
 */


namespace CFFI;

interface Unsigned
{
    const ID_NAME = 'unsigned';
}
interface Signed
{
    const ID_NAME = 'signed';
}
interface Long
{
    const ID_NAME = 'long';
}
interface Short
{
    const ID_NAME = 'short';
}
interface CCallType
{
    const ID_NAME = 'extern';
}

interface Fastcall extends CCallType
{
    const NAME = '__fastcall';
}
interface Stdcall extends CCallType
{
    const NAME = '__stdcall';
}
interface Vectorcall extends CCallType
{
    const NAME = '__vectorcall';
}
interface ReturnPtr
{
    public static function ptrLevel(): string;
}
