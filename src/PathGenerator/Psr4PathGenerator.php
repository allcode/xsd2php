<?php

namespace GoetasWebservices\Xsd\XsdToPhp\PathGenerator;

abstract class Psr4PathGenerator
{
    /**
     * @var array|string[]
     */
    protected $namespaces = [];

    public function __construct(array $targets = [])
    {
        $this->setTargets($targets);
    }

    public function setTargets(array $namespaces): void
    {
        $this->namespaces = $namespaces;

        foreach ($this->namespaces as $namespace => $dir) {
            if (!is_dir($dir)) {
                mkdir($dir, 0777, true);
            }
        }
    }
}
