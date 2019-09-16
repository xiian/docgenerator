<?php
declare(strict_types=1);

namespace xiian\docgenerator;

use phpDocumentor\Reflection\DocBlock\{Description, Tag};
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

    public function __toString()
    {
        // TODO: This could all be done in another Formatter!
        $rawString = $this->getSummary() . PHP_EOL . PHP_EOL . wordwrap($this->getDescription()->render(), 67, PHP_EOL, false);

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

    public function addDescription(string $in): self
    {
        $this->description = new Description($this->getDescription()->render() . PHP_EOL . $in);
        return $this;
    }

    public function addTag(Tag $tag): self
    {
        $this->tags[] = $tag;
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

    public function getTagsCollection():TagsCollection
    {
        return $this->tags;
    }

    public function setTagsCollection(TagsCollection $tags): self
    {
        $this->tags = $tags;
        return $this;
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

    public function hasTag(string $name): bool
    {
        return count($this->getTagsByName($name)) === 0;
    }

    public function removeTag(Tag $tagToRemove): void
    {

    }
}
