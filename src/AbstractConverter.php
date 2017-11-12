<?php

namespace GoetasWebservices\Xsd\XsdToPhp;

use GoetasWebservices\XML\XSDReader\Schema\Attribute\AttributeItem;
use GoetasWebservices\XML\XSDReader\Schema\Element\ElementSingle;
use GoetasWebservices\XML\XSDReader\Schema\Item;
use GoetasWebservices\XML\XSDReader\Schema\Schema;
use GoetasWebservices\XML\XSDReader\Schema\Type\ComplexType;
use GoetasWebservices\XML\XSDReader\Schema\Type\SimpleType;
use GoetasWebservices\XML\XSDReader\Schema\Type\Type;
use GoetasWebservices\Xsd\XsdToPhp\Naming\NamingStrategy;
use Psr\Log\LoggerAwareTrait;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

abstract class AbstractConverter
{
    use LoggerAwareTrait;

    protected $baseSchemas = [
        'http://www.w3.org/2001/XMLSchema',
        'http://www.w3.org/XML/1998/namespace',
    ];

    protected $namespaces = [
        'http://www.w3.org/2001/XMLSchema' => '',
        'http://www.w3.org/XML/1998/namespace' => '',
    ];

    /**
     * @var \GoetasWebservices\Xsd\XsdToPhp\Naming\NamingStrategy
     */
    private $namingStrategy;

    abstract public function convert(array $schemas): array;

    protected $typeAliases = [];

    protected $aliasCache = [];

    public function addAliasMap(string $ns, string $name, callable $handler): void
    {
        $this->logger->info("Added map $ns $name");
        $this->typeAliases[$ns][$name] = $handler;
    }

    public function addAliasMapType(string $ns, string $name, string $type): void
    {
        $this->addAliasMap($ns, $name, function () use ($type) {
            return $type;
        });
    }

    /**
     * @param Item|AttributeItem|Type $type
     *
     * @return mixed
     */
    public function getTypeAlias($type, Schema $schemapos = null)
    {
        $schema = $schemapos ?: $type->getSchema();

        $cid = $schema->getTargetNamespace() . '|' . $type->getName();
        if (isset($this->aliasCache[$cid])) {
            return $this->aliasCache[$cid];
        }
        if (isset($this->typeAliases[$schema->getTargetNamespace()][$type->getName()])) {
            return $this->aliasCache[$cid] = call_user_func(
                $this->typeAliases[$schema->getTargetNamespace()][$type->getName()],
                $type
            );
        }

        return null;
    }

    public function __construct(
        NamingStrategy $namingStrategy,
        LoggerInterface $logger = null
    ) {
        $this->namingStrategy = $namingStrategy;
        $this->logger = $logger ?: new NullLogger();

        $ns = 'http://www.w3.org/2001/XMLSchema';
        $typeMap = [
            'integer' => [
                'gYearMonth',
                'gMonthDay',
                'gMonth',
                'gYear',
                'integer',
                'int',
                'unsignedInt',
                'negativeInteger',
                'positiveInteger',
                'nonNegativeInteger',
                'nonPositiveInteger',
                'long',
                'unsignedLong',
                'short',
            ],
            'string' => [
                'string',
                'normalizedString',
                'language',
                'token',
                'anyURI',
                'byte',
                'ID',
                'IDREF',
                'IDREFS',
                'Name',
                'NCName',
                'QName',
                'NMTOKEN',
                'NMTOKENS',
            ],
            'float' => ['decimal', 'float', 'double'],
            'boolean' => ['boolean'],
            'DateInterval' => ['duration'],
        ];
        $this->addAliasMapTypesFromArray($ns, $typeMap);
    }

    protected function addAliasMapTypesFromArray(string $ns, array $typeMap): void
    {
        foreach ($typeMap as $type => $names) {
            foreach ($names as $name) {
                $this->addAliasMapType($ns, $name, $type);
            }
        }
    }

    protected function getNamingStrategy(): NamingStrategy
    {
        return $this->namingStrategy;
    }

    /**
     * @return $this
     */
    public function addNamespace(string $ns, string $phpNamespace)
    {
        $this->logger->info("Added ns mapping $ns, $phpNamespace");
        $this->namespaces[$ns] = $phpNamespace;

        return $this;
    }

    protected function cleanName(string $name): string
    {
        return preg_replace('/<.*>/', '', $name);
    }

    protected function isArrayType(Type $type): ?SimpleType
    {
        if ($type instanceof SimpleType) {
            return $type->getList();
        }

        return null;
    }

    protected function isArrayNestedElement(Type $type): ?ElementSingle
    {
        if ($type instanceof ComplexType
            && !$type->getParent()
            && !$type->getAttributes()
            && count($type->getElements()) === 1
        ) {
            $elements = $type->getElements();

            return $this->isArrayElement(reset($elements));
        }

        return null;
    }

    /**
     * @param mixed $element
     */
    protected function isArrayElement($element): ?ElementSingle
    {
        if ($element instanceof ElementSingle
            && ($element->getMax() > 1 || $element->getMax() === -1)
        ) {
            return $element;
        }

        return null;
    }

    public function getNamespaces(): array
    {
        return $this->namespaces;
    }
}
