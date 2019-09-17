<?php
declare(strict_types=1);

namespace xiian\docgenerator\Tags;

trait AnnotationTrait
{
    private $_annotations = [];

    public function addAnnotation(string $annotation): self
    {
        $this->_annotations[] = $annotation;
        return $this;
    }

    public function getAnnotations(): array
    {
        return $this->_annotations;
    }

    public function setAnnotations(array $annotations): self
    {
        $this->_annotations = $annotations;
        return $this;
    }

}
