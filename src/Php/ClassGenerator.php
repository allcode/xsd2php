<?php

namespace GoetasWebservices\Xsd\XsdToPhp\Php;

use Doctrine\Common\Inflector\Inflector;
use GoetasWebservices\Xsd\XsdToPhp\Php\Structure\PHPClass;
use GoetasWebservices\Xsd\XsdToPhp\Php\Structure\PHPClassOf;
use GoetasWebservices\Xsd\XsdToPhp\Php\Structure\PHPProperty;
use Zend\Code\Generator\ClassGenerator as ZendClassGenerator;
use Zend\Code\Generator\DocBlock\Tag\ParamTag;
use Zend\Code\Generator\DocBlock\Tag\ReturnTag;
use Zend\Code\Generator\DocBlock\Tag\VarTag;
use Zend\Code\Generator\DocBlockGenerator;
use Zend\Code\Generator\MethodGenerator;
use Zend\Code\Generator\ParameterGenerator;
use Zend\Code\Generator\PropertyGenerator;
use Zend\Code\Generator\PropertyValueGenerator;

class ClassGenerator
{
    private function handleBody(ZendClassGenerator $class, PHPClass $type): bool
    {
        foreach ($type->getProperties() as $prop) {
            if ($prop->getName() !== '__value') {
                $this->handleProperty($class, $prop);
            }
        }
        foreach ($type->getProperties() as $prop) {
            if ($prop->getName() !== '__value') {
                $this->handleMethod($class, $prop, $type);
            }
        }

        if (count($type->getProperties()) === 1 && $type->hasProperty('__value')) {
            return false;
        }

        return true;
    }

    private function isNativeType(PHPClass $class): bool
    {
        return !$class->getNamespace() && in_array($class->getName(), [
                'string',
                'int',
                'float',
                'integer',
                'boolean',
                'array',
                'mixed',
                'callable',
            ]);
    }

    private function handleValueMethod(
        ZendClassGenerator $generator,
        PHPProperty $prop,
        PHPClass $class,
        bool $all = true
    ): void {
        $type = $prop->getType();

        $docblock = new DocBlockGenerator('Construct');
        $paramTag = new ParamTag('value', 'mixed');
        $paramTag->setTypes(($type ? $type->getPhpType() : 'mixed'));

        $docblock->setTag($paramTag);

        $param = new ParameterGenerator('value');
        if ($type && $type->getPhpType() !== 'mixed') {
            $param->setType($type->getPhpType());
        }
        $method = new MethodGenerator('__construct', [
            $param,
        ]);
        $method->setDocBlock($docblock);
        $method->setBody('$this->value($value);');

        $generator->addMethodFromGenerator($method);

        $docblock = new DocBlockGenerator('Gets or sets the inner value');
        $paramTag = new ParamTag('value', 'mixed');
        if ($type && $type instanceof PHPClassOf) {
            $paramTag->setTypes($type->getArg()->getType()->getPhpType() . '[]');
        } elseif ($type) {
            $paramTag->setTypes($prop->getType()->getPhpType());
        }
        $docblock->setTag($paramTag);

        $returnTag = new ReturnTag('mixed');

        if ($type && $type instanceof PHPClassOf) {
            $returnTag->setTypes($type->getArg()->getType()->getPhpType() . '[]');
        } elseif ($type) {
            $returnTag->setTypes($type->getPhpType());
        }
        $docblock->setTag($returnTag);

        $param = new ParameterGenerator('value');
        $param->setDefaultValue(null);

        if ($type && $type->getPhpType() !== 'mixed') {
            $param->setType($type->getPhpType());
        }
        $method = new MethodGenerator('value', []);
        $method->setDocBlock($docblock);

        $methodBody = 'if ($args = func_get_args()) {' . PHP_EOL;
        $methodBody .= '    $this->' . $prop->getName() . ' = $args[0];' . PHP_EOL;
        $methodBody .= '}' . PHP_EOL;
        $methodBody .= 'return $this->' . $prop->getName() . ';' . PHP_EOL;
        $method->setBody($methodBody);

        $generator->addMethodFromGenerator($method);

        $docblock = new DocBlockGenerator('Gets a string value');
        $docblock->setTag(new ReturnTag('string'));
        $method = new MethodGenerator('__toString');
        $method->setDocBlock($docblock);
        $method->setBody('return strval($this->' . $prop->getName() . ');');
        $generator->addMethodFromGenerator($method);
    }

    private function handleSetter(
        ZendClassGenerator $generator,
        PHPProperty $prop,
        PHPClass $class
    ): void {
        $methodBody = '';
        $docblock = new DocBlockGenerator();

        $docblock->setShortDescription('Sets a new ' . $prop->getName());

        if ($prop->getDoc()) {
            $docblock->setLongDescription($prop->getDoc());
        }

        $paramTag = new ParamTag($prop->getName());
        $docblock->setTag($paramTag);

        $return = new ReturnTag('self');
        $docblock->setTag($return);

        $type = $prop->getType();

        $method = new MethodGenerator('set' . Inflector::classify($prop->getName()));

        $parameter = new ParameterGenerator($prop->getName());

        if ($type && $type instanceof PHPClassOf) {
            $paramTag->setTypes($type->getArg()
                    ->getType()->getPhpType() . '[]');
            $parameter->setType('array');

            if ($p = $type->getArg()->getType()->isSimpleType()
            ) {
                if (($t = $p->getType())) {
                    $paramTag->setTypes($t->getPhpType());
                }
            }
        } elseif ($type) {
            if ($type->isNativeType()) {
                $paramTag->setTypes($type->getPhpType());
                if ($type->getPhpType() !== 'mixed') {
                    $parameter->setType($type->getPhpType());
                }
            } elseif ($p = $type->isSimpleType()) {
                if (($t = $p->getType()) && !$t->isNativeType()) {
                    $paramTag->setTypes($t->getPhpType());
                    $parameter->setType($t->getPhpType());
                } elseif ($t && !$t->isNativeType()) {
                    $paramTag->setTypes($t->getPhpType());
                    $parameter->setType($t->getPhpType());
                } elseif ($t) {
                    $paramTag->setTypes($t->getPhpType());
                }
            } else {
                $paramTag->setTypes($type->getPhpType());
                $parameter->setType($type->getPhpType());
            }
        }

        $methodBody .= '$this->' . $prop->getName() . ' = $' . $prop->getName() . ';' . PHP_EOL;
        $methodBody .= 'return $this;';
        $method->setBody($methodBody);
        $method->setDocBlock($docblock);
        $method->setParameter($parameter);

        $generator->addMethodFromGenerator($method);
    }

    private function handleGetter(
        ZendClassGenerator $generator,
        PHPProperty $prop,
        PHPClass $class
    ): void {
        if ($prop->getType() instanceof PHPClassOf) {
            $docblock = new DocBlockGenerator();
            $docblock->setShortDescription('isset ' . $prop->getName());
            if ($prop->getDoc()) {
                $docblock->setLongDescription($prop->getDoc());
            }

            $paramTag = new ParamTag('index', 'string|int');
            $docblock->setTag($paramTag);

            $docblock->setTag(new ReturnTag('boolean'));

            $paramIndex = new ParameterGenerator('index');

            $method = new MethodGenerator(
                'isset' . Inflector::classify($prop->getName()),
                [$paramIndex]
            );
            $method->setDocBlock($docblock);
            $method->setBody('return isset($this->' . $prop->getName() . '[$index]);');
            $method->setReturnType('bool');
            $generator->addMethodFromGenerator($method);

            $docblock = new DocBlockGenerator();
            $docblock->setShortDescription('unset ' . $prop->getName());
            if ($prop->getDoc()) {
                $docblock->setLongDescription($prop->getDoc());
            }

            $paramTag = new ParamTag('index', 'string|int');
            $docblock->setTag($paramTag);
            $paramIndex = new ParameterGenerator('index');

            $docblock->setTag(new ReturnTag('void'));

            $method = new MethodGenerator('unset' . Inflector::classify($prop->getName()),
                [$paramIndex]);
            $method->setDocBlock($docblock);
            $method->setBody('unset($this->' . $prop->getName() . '[$index]);');
            $method->setReturnType('void');
            $generator->addMethodFromGenerator($method);
        }

        $docblock = new DocBlockGenerator();

        $docblock->setShortDescription('Gets as ' . $prop->getName());

        if ($prop->getDoc()) {
            $docblock->setLongDescription($prop->getDoc());
        }

        $tag = new ReturnTag('mixed');
        $type = $prop->getType();
        if ($type && $type instanceof PHPClassOf) {
            $tt = $type->getArg()->getType();
            $tag->setTypes($tt->getPhpType() . '[]');
            if ($p = $tt->isSimpleType()) {
                if (($t = $p->getType())) {
                    $tag->setTypes($t->getPhpType() . '[]');
                }
            }
        } elseif ($type) {
            if ($p = $type->isSimpleType()) {
                if ($t = $p->getType()) {
                    $tag->setTypes($t->getPhpType());
                }
            } else {
                $tag->setTypes($type->getPhpType());
            }
        }

        $docblock->setTag($tag);

        $method = new MethodGenerator('get' . Inflector::classify($prop->getName()));
        $method->setDocBlock($docblock);
        $method->setBody('return $this->' . $prop->getName() . ';');
        if ($type && $type->getPhpType() !== 'mixed') {
            $method->setReturnType('?' . $type->getPhpType());
        }

        $generator->addMethodFromGenerator($method);
    }

    private function handleAdder(
        ZendClassGenerator $generator,
        PHPProperty $prop,
        PHPClass $class
    ): void {
        $type = $prop->getType();
        assert($type instanceof PHPClassOf);
        $propName = $type->getArg()->getName();

        $docblock = new DocBlockGenerator();
        $docblock->setShortDescription("Adds as $propName");

        if ($prop->getDoc()) {
            $docblock->setLongDescription($prop->getDoc());
        }

        $return = new ReturnTag();
        $return->setTypes('self');
        $docblock->setTag($return);

        $paramTag = new ParamTag($propName, $type->getArg()->getType()->getPhpType());
        $docblock->setTag($paramTag);

        $method = new MethodGenerator('addTo' . Inflector::classify($prop->getName()));

        $parameter = new ParameterGenerator($propName);
        $tt = $type->getArg()->getType();

        if (!$tt->isNativeType()) {
            if ($p = $tt->isSimpleType()) {
                if (($t = $p->getType())) {
                    $paramTag->setTypes($t->getPhpType());

                    if ($t->getPhpType() !== 'mixed') {
                        $parameter->setType($t->getPhpType());
                    }
                }
            } elseif (!$tt->isNativeType()) {
                $parameter->setType($tt->getPhpType());
            }
        }

        $methodBody = '$this->' . $prop->getName() . '[] = $' . $propName . ';' . PHP_EOL;
        $methodBody .= 'return $this;';
        $method->setBody($methodBody);
        $method->setDocBlock($docblock);
        $method->setParameter($parameter);
        $method->setReturnType($class->getFullName());
        $generator->addMethodFromGenerator($method);
    }

    private function handleMethod(
        ZendClassGenerator $generator,
        PHPProperty $prop,
        PHPClass $class
    ): void {
        if ($prop->getType() instanceof PHPClassOf) {
            $this->handleAdder($generator, $prop, $class);
        }

        $this->handleGetter($generator, $prop, $class);
        $this->handleSetter($generator, $prop, $class);
    }

    private function handleProperty(
        ZendClassGenerator $class,
        PHPProperty $prop
    ): void {
        $generatedProp = new PropertyGenerator($prop->getName());
        $generatedProp->setVisibility(PropertyGenerator::VISIBILITY_PRIVATE);

        $class->addPropertyFromGenerator($generatedProp);

        $docBlock = new DocBlockGenerator();
        $generatedProp->setDocBlock($docBlock);

        if ($prop->getDoc()) {
            $docBlock->setLongDescription($prop->getDoc());
        }
        $tag = new VarTag(null, 'mixed');

        $type = $prop->getType();

        if ($type && $type instanceof PHPClassOf) {
            $tt = $type->getArg()->getType();
            $tag->setTypes($tt->getPhpType() . '[]');
            if ($p = $tt->isSimpleType()) {
                if (($t = $p->getType())) {
                    $tag->setTypes($t->getPhpType() . '[]');
                }
            }
            $defaultValue = $type->getArg()->getDefault();
            $generatedProp->setDefaultValue(
                $defaultValue,
                PropertyValueGenerator::TYPE_AUTO,
                PropertyValueGenerator::OUTPUT_SINGLE_LINE
            );
            if (!empty($defaultValue) && $this->isLongPropertyDefaultValue($generatedProp)) {
                $generatedProp
                    ->getDefaultValue()
                    ->setOutputMode(PropertyValueGenerator::OUTPUT_MULTIPLE_LINE);
            }
        } elseif ($type) {
            if ($type->isNativeType()) {
                $tag->setTypes($type->getPhpType());
            } elseif (($p = $type->isSimpleType()) && ($t = $p->getType())) {
                $tag->setTypes($t->getPhpType());
            } else {
                $tag->setTypes($prop->getType()->getPhpType());
            }
        }
        $docBlock->setTag($tag);
    }

    public function generate(PHPClass $type): ?ZendClassGenerator
    {
        $class = new ZendClassGenerator();
        $docblock = new DocBlockGenerator('Class representing ' . $type->getName());
        if ($type->getDoc()) {
            $docblock->setLongDescription($type->getDoc());
        }
        $class->setNamespaceName($type->getNamespace() ?: null);
        $class->setName($type->getName());
        $class->setDocBlock($docblock);

        if ($extends = $type->getExtends()) {
            if ($p = $extends->isSimpleType()) {
                $this->handleProperty($class, $p);
                $this->handleValueMethod($class, $p, $extends);
            } else {
                $class->setExtendedClass($extends->getFullName());
            }
        }

        if ($this->handleBody($class, $type)) {
            return $class;
        }

        return null;
    }

    private function isLongPropertyDefaultValue(
        PropertyGenerator $propertyGenerator
    ): bool {
        $property = array_pop(explode("\n", $propertyGenerator->generate()));

        return strlen($property) > 80;
    }
}
