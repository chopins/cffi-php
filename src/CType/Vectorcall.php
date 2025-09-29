<?php

namespace CFFI\CType;

#[\Attribute(\Attribute::TARGET_METHOD)]
interface Vectorcall {
    const NAME = 'vectorcall';
}