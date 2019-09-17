<?php
declare(strict_types=1);

namespace xiian\docgenerator;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use phpDocumentor\Reflection\DocBlock\Tags\Property;
use Ramsey\Collection\AbstractCollection;
use xiian\docgenerator\Tags\Annotatable;

class TagsCollection extends AbstractCollection
{
    /**
     * Indexed by the name of the tag
     *
     * @var Tag[]
     */
    protected $_indexedByName = [];

    private $_indexedByAnnotation = [];

    public function findMatching(Tag $tag)
    {
        return $this->filter(function (Tag $t) use ($tag) {
            if ($t->getName() !== $tag->getName()) {
                return false;
            }

            if ($t instanceof Property && $tag instanceof Property) {
                if ($t->getVariableName() !== $tag->getVariableName()) {
                    return false;
                }
            }

            if ($t instanceof Method && $tag instanceof Method) {
                if ($t->getMethodName() !== $tag->getMethodName()) {
                    return false;
                }
            }

            return true;
        });
    }

    public function getByAnnotation(): array
    {
        return $this->_indexedByAnnotation;
    }

    public function getByName(): array
    {
        return $this->_indexedByName;
    }

    public function getType(): string
    {
        return Tag::class;
    }

    public function offsetSet($offset, $value): void
    {
        parent::offsetSet($offset, $value);

        // Safety check
        if (!($value instanceof Tag)) {
            return;
        }

        // Index by name
        $name = $value->getName();
        if (!array_key_exists($name, $this->_indexedByName) || !is_array($this->_indexedByName[$name])) {
            $this->_indexedByName[$name] = [];
        }
        $this->_indexedByName[$name][] = $value;

        // Index by annotation, if possible
        if ($value instanceof Annotatable) {
            foreach ($value->getAnnotations() as $annotation) {
                if (!array_key_exists($annotation, $this->_indexedByAnnotation) || !is_array($this->_indexedByAnnotation[$annotation])) {
                    $this->_indexedByAnnotation[$annotation] = [];
                }
                $this->_indexedByAnnotation[$annotation][] = $value;
            }
        } else {
            $this->_indexedByAnnotation[0][] = $value;
        }
    }
}
