<?php
declare(strict_types=1);

namespace xiian\docgenerator\Tags;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\Tags\BaseTag;
use phpDocumentor\Reflection\Type;
use phpDocumentor\Reflection\TypeResolver;
use phpDocumentor\Reflection\Types\Context as TypeContext;
use phpDocumentor\Reflection\Types\Void_;
use Webmozart\Assert\Assert;
use xiian\docgenerator\Argument;

/**
 * Copied wholesale from \phpDocumentor\Reflection\DocBlock\Tags\Method because it's marked as final.
 */
class Method extends BaseTag implements Annotatable
{
    use AnnotationTrait;

    protected $name = 'method';

    /** @var \phpDocumentor\Reflection\DocBlock\Tags\Method */
    private $_wrapped;

    /** @var string */
    private $methodName = '';

    /** @var Argument[] */
    private $arguments = [];

    /** @var bool */
    private $isStatic = false;

    /** @var Type */
    private $returnType;

    public function __construct(
        $methodName,
        array $arguments = [],
        Type $returnType = null,
        $static = false,
        Description $description = null
    ) {
        Assert::stringNotEmpty($methodName);
        Assert::boolean($static);

        if ($returnType === null) {
            $returnType = new Void_();
        }

        $this->methodName  = $methodName;
        $this->arguments   = $this->filterArguments($arguments);
        $this->returnType  = $returnType;
        $this->isStatic    = $static;
        $this->description = $description;
    }

    /**
     * @param $arguments
     *
     * @return Argument[]
     */
    private function filterArguments($arguments): array
    {
        foreach ($arguments as &$argument) {
            if ($argument instanceof Argument) {
                continue;
            }

            if (is_string($argument)) {
                $argument = ['name' => $argument];
            }

            if (is_array($argument)) {
                $argument = new Argument($argument['name'], $argument['type'] ?? new Void_());
            }
        }

        return $arguments;
    }

    /**
     * {@inheritdoc}
     */
    public static function create(
        $body,
        TypeResolver $typeResolver = null,
        DescriptionFactory $descriptionFactory = null,
        TypeContext $context = null
    ) {
        Assert::stringNotEmpty($body);
        Assert::allNotNull([$typeResolver, $descriptionFactory]);

        // 1. none or more whitespace
        // 2. optionally the keyword "static" followed by whitespace
        // 3. optionally a word with underscores followed by whitespace : as
        //    type for the return value
        // 4. then optionally a word with underscores followed by () and
        //    whitespace : as method name as used by phpDocumentor
        // 5. then a word with underscores, followed by ( and any character
        //    until a ) and whitespace : as method name with signature
        // 6. any remaining text : as description
        if (!preg_match(
            '/^
                # Static keyword
                # Declares a static method ONLY if type is also present
                (?:
                    (static)
                    \s+
                )?
                # Return type
                (?:
                    (   
                        (?:[\w\|_\\\\]*\$this[\w\|_\\\\]*)
                        |
                        (?:
                            (?:[\w\|_\\\\]+)
                            # array notation           
                            (?:\[\])*
                        )*
                    )
                    \s+
                )?
                # Legacy method name (not captured)
                (?:
                    [\w_]+\(\)\s+
                )?
                # Method name
                ([\w\|_\\\\]+)
                # Arguments
                (?:
                    \(([^\)]*)\)
                )?
                \s*
                # Description
                (.*)
            $/sux',
            $body,
            $matches
        )) {
            return null;
        }

        list(, $static, $returnType, $methodName, $arguments, $description) = $matches;

        $static = $static === 'static';

        if ($returnType === '') {
            $returnType = 'void';
        }

        $returnType  = $typeResolver->resolve($returnType, $context);
        $description = $descriptionFactory->create($description, $context);

        if (is_string($arguments) && strlen($arguments) > 0) {
            $arguments = explode(',', $arguments);
            foreach ($arguments as &$argument) {
                $argument = explode(' ', self::stripRestArg(trim($argument)), 2);
                if ($argument[0][0] === '$') {
                    $argumentName = substr($argument[0], 1);
                    $argumentType = new Void_();
                } else {
                    $argumentType = $typeResolver->resolve($argument[0], $context);
                    $argumentName = '';
                    if (isset($argument[1])) {
                        $argument[1]  = self::stripRestArg($argument[1]);
                        $argumentName = substr($argument[1], 1);
                    }
                }

                $argument = ['name' => $argumentName, 'type' => $argumentType];
            }
        } else {
            $arguments = [];
        }

        return new static($methodName, $arguments, $returnType, $static, $description);
    }

    private static function stripRestArg($argument)
    {
        if (strpos($argument, '...') === 0) {
            $argument = trim(substr($argument, 3));
        }

        return $argument;
    }

    public function __toString()
    {
        $arguments = [];
        foreach ($this->arguments as $argument) {
            $arguments[] = $argument->getType() . ' $' . $argument->getName();
        }

        return trim(($this->isStatic() ? 'static ' : '')
            . (string) $this->returnType . ' '
            . $this->methodName
            . '(' . implode(', ', $arguments) . ')'
            . ($this->description ? ' ' . $this->description->render() : ''));
    }

    /**
     * Checks whether the method tag describes a static method or not.
     *
     * @return bool TRUE if the method declaration is for a static method, FALSE otherwise.
     */
    public function isStatic()
    {
        return $this->isStatic;
    }

    /**
     * @return string[]
     */
    public function getArguments(): array
    {
        return array_map(function (Argument $a) {
            return (string) $a;
        }, $this->arguments);
    }

    /**
     * Retrieves the method name.
     *
     * @return string
     */
    public function getMethodName()
    {
        return $this->methodName;
    }

    /**
     * @return Type
     */
    public function getReturnType()
    {
        return $this->returnType;
    }
}
