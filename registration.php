<?php

if (class_exists(\Magento\Framework\Component\ComponentRegistrar::class)) {
    \Magento\Framework\Component\ComponentRegistrar::register(
        \Magento\Framework\Component\ComponentRegistrar::MODULE,
        'OuterEdge_Multiplenewsletter',
        __DIR__
    );
}