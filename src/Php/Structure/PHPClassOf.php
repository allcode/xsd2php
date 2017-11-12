<?php

namespace GoetasWebservices\Xsd\XsdToPhp\Php\Structure;

class PHPClassOf extends PHPClass
{
    /**
     * @var PHPArg
     */
    protected $arg;

    /**
     * @param PHPArg $arg
     */
    public function __construct(PHPArg $arg)
    {
        parent::__construct('array');
        $this->arg = $arg;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return 'array of ' . $this->arg->getName();
    }

    /**
     * @return PHPArg
     */
    public function getArg()
    {
        return $this->arg;
    }
}
