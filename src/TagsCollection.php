<?php
declare(strict_types=1);

namespace xiian\docgenerator;

use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\{Method, Property, PropertyRead, PropertyWrite};
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

    public static $aliases = [
        'property-read'  => 'property',
        'property-write' => 'property',
    ];

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

    public function getByName($sorted = false): array
    {
        if ($sorted) {
            return array_map(function ($r) {
                usort($r, [$this, 'sortTags']);
                return $r;
            }, $this->_indexedByName);
        }
        return $this->_indexedByName;
    }

    public function sortTags(Tag $a, Tag $b)
    {
        $aEffectiveName = $this->deAliasTagName($a->getName());
        $bEffectiveName = $this->deAliasTagName($b->getName());

        if ($aEffectiveName == $bEffectiveName) {
            if (
                ($a instanceof Property || $a instanceof PropertyRead || $a instanceof PropertyWrite)
                &&
                ($b instanceof Property || $b instanceof PropertyRead || $b instanceof PropertyWrite)
            ) {
                return strtolower($a->getVariableName()) <=> strtolower($b->getVariableName());
            }
            if ($a instanceof Method && $b instanceof Method) {
                return strtolower($a->getMethodName()) <=> strtolower($b->getMethodName());
            }
            if ($a instanceof Tags\Method && $b instanceof Tags\Method) {
                return strtolower($a->getMethodName()) <=> strtolower($b->getMethodName());
            }
        }

        return $aEffectiveName <=> $bEffectiveName;
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

        // Index by (aliased) name
        $name = $this->deAliasTagName((string) $value->getName());

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

    public function deAliasTagName(string $name): string
    {
        if (array_key_exists($name, self::$aliases)) {
            $name = self::$aliases[$name];
        }
        return $name;
    }


}
