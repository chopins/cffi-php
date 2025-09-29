<?php

namespace CFFI\CType;

#[\Attribute(\Attribute::TARGET_METHOD)]
interface Stdcall {
    const NAME = 'stdcall';
}