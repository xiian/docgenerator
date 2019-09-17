<?php
declare(strict_types=1);

namespace xiian\docgenerator\Tags;

interface Annotatable
{
    /**
     * @param string $annotation
     *
     * @return self
     */
    public function addAnnotation(string $annotation);

    /**
     * @return string[]
     */
    public function getAnnotations(): array;

    /**
     * @param string[] $annotations
     *
     * @return self
     */
    public function setAnnotations(array $annotations);
}
