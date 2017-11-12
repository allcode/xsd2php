<?php

namespace GoetasWebservices\Xsd\XsdToPhp\Naming;

use GoetasWebservices\XML\XSDReader\Schema\Attribute\AttributeItem;
use GoetasWebservices\XML\XSDReader\Schema\Item;
use GoetasWebservices\XML\XSDReader\Schema\Type\Type;
use GoetasWebservices\Xsd\XsdToPhp\Php\Structure\PHPArg;

interface NamingStrategy
{
    public function getTypeName(Type $type): string;

    public function getAnonymousTypeName(Type $type, string $parentName): string;

    public function getItemName(Item $item): string;

    /**
     * @todo introduce common type for attributes and elements
     *
     * @param Item|AttributeItem|PHPArg $item
     *
     * @return string
     */
    public function getPropertyName($item): string;
}
