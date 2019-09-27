<?php

namespace xiian\docgenerator;

use phpDocumentor\Reflection\Type;

class Argument
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var Type
     */
    protected $type;

    /**
     * @var string
     */
    protected $defaultValue;

    public function __construct(string $name, Type $type, string $defaultValue = null)
    {
        $this->name         = $name;
        $this->type         = $type;
        $this->defaultValue = $defaultValue;
    }

    public function __toString()
    {
        $out = ((string) $this->type) . ' $' . $this->name;

        if ($this->defaultValue) {
            $out .= ' = ' . $this->defaultValue;
        }

        return trim($out);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return Type
     */
    public function getType(): Type
    {
        return $this->type;
    }


}
