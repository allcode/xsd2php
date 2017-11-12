<?php

namespace GoetasWebservices\Xsd\XsdToPhp\Naming;

use Doctrine\Common\Inflector\Inflector;
use GoetasWebservices\XML\XSDReader\Schema\Item;
use GoetasWebservices\XML\XSDReader\Schema\Type\Type;

class LongNamingStrategy implements NamingStrategy
{
    protected $reservedWords = [
        'int',
        'float',
        'bool',
        'string',
        'true',
        'false',
        'null',
        'resource',
        'object',
        'mixed',
        'numeric',
    ];

    public function getTypeName(Type $type): string
    {
        return $this->classify($type->getName()) . 'Type';
    }

    public function getAnonymousTypeName(Type $type, string $parentName): string
    {
        return $this->classify($parentName) . 'AnonymousType';
    }

    public function getItemName(Item $item): string
    {
        $name = $this->classify($item->getName());
        if (in_array(strtolower($name), $this->reservedWords)) {
            $name .= 'Xsd';
        }

        return $name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPropertyName($item): string
    {
        return Inflector::camelize(str_replace('.', ' ', $item->getName()));
    }

    private function classify(?string $name): string
    {
        if ($name === null) {
            return '';
        }

        return Inflector::classify(str_replace('.', ' ', $name));
    }
}
