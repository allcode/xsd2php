<?php

namespace GoetasWebservices\Xsd\XsdToPhp\Php\Structure;

class PHPArg
{
    /**
     * @var string|null
     */
    protected $doc;

    /**
     * @var PHPClass|null
     */
    protected $type;

    /**
     * @var string|null
     */
    protected $name;

    /**
     * @var mixed|null
     */
    protected $default;

    public function __construct(string $name = null, PHPClass $type = null)
    {
        $this->name = $name;
        $this->type = $type;
    }

    public function getDoc(): ?string
    {
        return $this->doc;
    }

    /**
     * @return $this
     */
    public function setDoc(?string $doc)
    {
        $this->doc = $doc;

        return $this;
    }

    public function getType(): ?PHPClass
    {
        return $this->type;
    }

    /**
     * @return $this
     */
    public function setType(PHPClass $type)
    {
        $this->type = $type;

        return $this;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @return $this
     */
    public function setName(string $name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return mixed|null
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     *
     * @return $this
     */
    public function setDefault($default)
    {
        $this->default = $default;

        return $this;
    }
}
