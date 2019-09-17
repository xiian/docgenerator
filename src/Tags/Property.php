<?php
declare(strict_types=1);

namespace xiian\docgenerator\Tags;

class Property extends \phpDocumentor\Reflection\DocBlock\Tags\Property implements Annotatable
{
    use AnnotationTrait;
}
