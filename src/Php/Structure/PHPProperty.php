<?php

namespace GoetasWebservices\Xsd\XsdToPhp\Php\Structure;

class PHPProperty extends PHPArg
{
    /**
     * @var string
     */
    protected $visibility = 'protected';

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    /**
     * @return $this
     */
    public function setVisibility(string $visibility)
    {
        $this->visibility = $visibility;

        return $this;
    }
}
