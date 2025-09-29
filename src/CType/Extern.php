<?php

namespace CFFI\CType;

#[\Attribute(\Attribute::TARGET_METHOD| \Attribute::TARGET_PROPERTY)]
interface Extern {
    const NAME = 'extern';
}