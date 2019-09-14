<?php

namespace xiian\docgenerator;

use phpDocumentor\Reflection\DocBlock\Tag;
use Ramsey\Collection\AbstractCollection;

class TagsCollection extends AbstractCollection
{
    public function getType(): string
    {
        return Tag::class;
    }

}
