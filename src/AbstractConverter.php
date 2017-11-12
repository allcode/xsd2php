<?php

namespace GoetasWebservices\Xsd\XsdToPhp;

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

    abstract public function convert(array $schemas);

    protected $typeAliases = [];

    protected $aliasCache = [];

    public function addAliasMap($ns, $name, callable $handler)
    {
        $this->logger->info("Added map $ns $name");
        $this->typeAliases[$ns][$name] = $handler;
    }

    public function addAliasMapType($ns, $name, $type)
    {
        $this->addAliasMap($ns, $name, function () use ($type) {
            return $type;
        });
    }

    /**
     * @param Item        $type
     * @param Schema|null $schemapos
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
    }

    public function __construct(
        NamingStrategy $namingStrategy,
        LoggerInterface $logger = null
    ) {
        $this->namingStrategy = $namingStrategy;
        $this->logger = $logger ?: new NullLogger();

        $ns = 'http://www.w3.org/2001/XMLSchema';
        $this->addAliasMap($ns, 'gYearMonth', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'gMonthDay', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'gMonth', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'gYear', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'NMTOKEN', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'NMTOKENS', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'QName', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'NCName', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'decimal', function (Type $type) {
            return 'float';
        });
        $this->addAliasMap($ns, 'float', function (Type $type) {
            return 'float';
        });
        $this->addAliasMap($ns, 'double', function (Type $type) {
            return 'float';
        });
        $this->addAliasMap($ns, 'string', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'normalizedString', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'integer', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'int', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'unsignedInt', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'negativeInteger', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'positiveInteger', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'nonNegativeInteger', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'nonPositiveInteger', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'long', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'unsignedLong', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'short', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'boolean', function (Type $type) {
            return 'boolean';
        });
        $this->addAliasMap($ns, 'nonNegativeInteger', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'positiveInteger', function (Type $type) {
            return 'integer';
        });
        $this->addAliasMap($ns, 'language', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'token', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'anyURI', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'byte', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'duration', function (Type $type) {
            return 'DateInterval';
        });

        $this->addAliasMap($ns, 'ID', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'IDREF', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'IDREFS', function (Type $type) {
            return 'string';
        });
        $this->addAliasMap($ns, 'Name', function (Type $type) {
            return 'string';
        });

        $this->addAliasMap($ns, 'NCName', function (Type $type) {
            return 'string';
        });
    }

    /**
     * @return \GoetasWebservices\Xsd\XsdToPhp\Naming\NamingStrategy
     */
    protected function getNamingStrategy()
    {
        return $this->namingStrategy;
    }

    /**
     * @param string $ns
     * @param string $phpNamespace
     *
     * @return $this
     */
    public function addNamespace($ns, $phpNamespace)
    {
        $this->logger->info("Added ns mapping $ns, $phpNamespace");
        $this->namespaces[$ns] = $phpNamespace;

        return $this;
    }

    /**
     * @param string $name
     *
     * @return mixed
     */
    protected function cleanName($name)
    {
        return preg_replace('/<.*>/', '', $name);
    }

    /**
     * @param Type $type
     *
     * @return \GoetasWebservices\XML\XSDReader\Schema\Type\Type|null
     */
    protected function isArrayType(Type $type)
    {
        if ($type instanceof SimpleType) {
            return $type->getList();
        }

        return null;
    }

    /**
     * @param Type $type
     *
     * @return \GoetasWebservices\XML\XSDReader\Schema\Element\ElementSingle|null
     */
    protected function isArrayNestedElement(Type $type)
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
     *
     * @return \GoetasWebservices\XML\XSDReader\Schema\Element\ElementSingle|null
     */
    protected function isArrayElement($element)
    {
        if ($element instanceof ElementSingle
            && ($element->getMax() > 1 || $element->getMax() === -1)
        ) {
            return $element;
        }

        return null;
    }

    /**
     * @return array
     */
    public function getNamespaces()
    {
        return $this->namespaces;
    }
}
