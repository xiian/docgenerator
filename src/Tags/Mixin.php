<?php
declare(strict_types=1);

namespace xiian\docgenerator\Tags;

use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\Type;

class Mixin extends BaseTag implements Annotatable
{
    use AnnotationTrait;

    protected $name = 'mixin';

    /** @var Type */
    protected $type;

    public function __construct(Type $type)
    {
        $this->type = $type;
    }

    public static function create($body)
    {
        throw new \BadMethodCallException('Need some body');
    }

    public function __toString()
    {
        return (string) $this->type;
    }

    public function getType(): Type
    {
        return $this->type;
    }

}
