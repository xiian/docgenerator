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

    /**
     * @var Tag[]
     */
    protected $tags;

    public function __toString()
    {
        $rawString = $this->summary . PHP_EOL . PHP_EOL . wordwrap($this->getDescription()->render(), 67, PHP_EOL, false);

        // Wrap with leading asterisks
        $implode = implode(PHP_EOL . ' * ', explode(PHP_EOL, $rawString));

        // Trim up line endings
        $wrapped = implode(PHP_EOL, array_map('rtrim', explode(PHP_EOL, $implode)));

        // Tags
        $formatter   = new AlignBetterFormatter($this->tags);
        $lastTagName = null;
        foreach ($this->tags as $tag) {
            if ($tag->getName() !== $lastTagName) {
                $wrapped     .= PHP_EOL . ' *';
                $lastTagName = $tag->getName();
            }
            $wrapped .= PHP_EOL . ' * ' . $formatter->format($tag);
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
        $this->description = new Description($this->description->render() . PHP_EOL . $in);
        return $this;
    }

    public function addTag(Tag $tag): self
    {
        $this->tags[] = $tag;
        return $this;
    }

    public function getSummary(): string
    {
        return $this->summary;
    }

    public function setSummary(string $in): self
    {
        $this->summary = $in;
        return $this;
    }

    /**
     * @return Tag[]
     */
    public function getTags(): array
    {
        return $this->tags;
    }

    /**
     * @param $name
     *
     * @return Tag[]
     */
    public function getTagsByName($name): array
    {
        return [];
    }

    public function hasTag(string $name): bool
    {
        return false;
    }

    public function removeTag(Tag $tagToRemove): void
    {

    }
}
