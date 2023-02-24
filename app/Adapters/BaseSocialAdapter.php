<?php

namespace App\Adapters;

abstract class BaseSocialAdapter
{
    /** @var array */
    protected $config;

    public function __construct(array $config = [])
    {
        $this->setConfig($config);
    }

    public function setConfig(array $config): void
    {
        $this->config = $config;
    }
}
