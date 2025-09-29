<?php

namespace CFFI\CType;

#[\Attribute(\Attribute::TARGET_METHOD)]
interface Fastcall {
    const NAME = 'fastcall';
}