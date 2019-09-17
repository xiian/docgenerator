<?php
declare(strict_types=1);

namespace xiian\docgenerator;

use phpDocumentor\Reflection\DocBlock\{Description, Tag, Tags\Method, Tags\Property};
use xiian\PHPDocFormatters\Tags\Formatter\AlignBetterFormatter;

class DocBlock
{
    /** @var Description */
    protected $description;

    /** @var string */
    protected $summary;

    /** @var TagsCollection|Tag[] */
    protected $tags;

    public function __construct()
    {
        $this->tags = new TagsCollection();
    }

    public static function fromReflectionDocBlock(\phpDocumentor\Reflection\DocBlock $docblock): self
    {
        $out = new self();
        $out->setSummary($docblock->getSummary());
        $out->setDescription($docblock->getDescription()->render());
        foreach ($docblock->getTags() as $tag) {
            $out->addTag($tag);
        }
        return $out;
    }

    public function addTag(Tag $tag): self
    {
        $this->tags[] = $tag;
        return $this;
    }

    public function __toString()
    {
        // TODO: This could all be done in another Formatter!
        $description = $this->getDescription();
        $rawString   = $this->getSummary() . PHP_EOL . PHP_EOL . wordwrap($description->render(), 67, PHP_EOL, false);

        $rawString = trim($rawString);

        // Wrap with leading asterisks
        $implode = implode(PHP_EOL . ' * ', explode(PHP_EOL, $rawString));

        // Trim up line endings
        $wrapped = implode(PHP_EOL, array_map('rtrim', explode(PHP_EOL, $implode)));

        // Tags
        $formatter   = new AlignBetterFormatter($this->getTags());
        $lastTagName = null;
        foreach ($this->getTags() as $tag) {
            if ($tag->getName() !== $lastTagName) {
                $wrapped     .= PHP_EOL . ' *';
                $lastTagName = $tag->getName();
            }
            $wrapped .= PHP_EOL . ' * ' . $formatter->format($tag);
        }

        $wrapped = trim($wrapped);

        if (empty($wrapped)) {
            return '/** */';
        }

        return '/**' . PHP_EOL . ' * ' . $wrapped . PHP_EOL . ' */';
    }

    public function getDescription(): Description
    {
        if (!$this->description) {
            $this->description = new Description('');
        }
        return $this->description;
    }

    public function setDescription(string $in): self
    {
        $this->description = new Description($in);
        return $this;
    }

    public function getSummary(): ?string
    {
        return $this->summary;
    }

    public function setSummary(?string $in): self
    {
        $this->summary = $in;
        return $this;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags->toArray();
    }

    public function addDescription(string $in): self
    {
        $this->description = new Description($this->getDescription()->render() . PHP_EOL . $in);
        return $this;
    }

    public function asReflectionDocBlock(): \phpDocumentor\Reflection\DocBlock
    {
        return new \phpDocumentor\Reflection\DocBlock(
            $this->getSummary(),
            $this->getDescription(),
            $this->getTags(),
        );
    }

    /**
     * @param bool $sortedTags
     *
     * @return Tag[][]
     */
    public function getTagGroups($sortedTags = false): array
    {
        $groups = [];
        foreach ($this->tags->getByAnnotation() as $name => $tags) {
            if ($sortedTags) {
                usort($tags, function (Tag $a, Tag $b) {
                    if ($a->getName() == $b->getName()) {
                        if ($a instanceof Property && $b instanceof Property) {
                            return $a->getVariableName() <=> $b->getVariableName();
                        }
                        if ($a instanceof Method && $b instanceof Method) {
                            return $a->getMethodName() <=> $b->getMethodName();
                        }
                    }
                    return $a->getName() <=> $b->getName();
                });
            }
            $groups[$name] = $tags;
        }
        return $groups;
    }

    public function getTagsCollection(): TagsCollection
    {
        return $this->tags;
    }

    public function hasTag(string $name): bool
    {
        return count($this->getTagsByName($name)) === 0;
    }

    /**
     * @param $name
     *
     * @return Tag[]
     */
    public function getTagsByName($name): array
    {
        return $this->tags->where('getName', $name);
    }

    /**
     * @param Tag[] $tags
     *
     * @throws \Exception
     */
    public function mergeTags(array $tags)
    {
        foreach ($tags as $incomingTag) {
            // Find existing one
            $existing = $this->tags->findMatching($incomingTag);
            if (count($existing) > 1) {
                throw new \Exception('Multiple matching tags found that matched ' . $incomingTag->getName());
            }
            if (count($existing)) {
                // Make sure types match
                /** @var Tag $matched */
                $matched = $existing[0];

                if ($matched instanceof Property && $incomingTag instanceof Property) {
                    if ((string) $matched->getType() !== (string) $incomingTag->getType()) {
                        throw new \Exception('Mismatched types for $' . $incomingTag->getVariableName() . '! Already had: ' . (string) $matched->getType() . ' and was given: ' . (string) $incomingTag->getType());
                    }
                }

                continue;
            }
            $this->addTag($incomingTag);
        }
    }

    public function removeTag(Tag $tagToRemove): void
    {

    }

    public function setTagsCollection(TagsCollection $tags): self
    {
        $this->tags = $tags;
        return $this;
    }
}
