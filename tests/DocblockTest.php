<?php
declare(strict_types=1);

namespace xiian\docgenerator\test;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tags\{Author, Param, Since};
use phpDocumentor\Reflection\Types\{Integer, String_};
use PHPUnit\Framework\TestCase;
use xiian\docgenerator\DocBlock;

/**
 * @coversDefaultClass \xiian\docgenerator\DocBlock
 */
class DocblockTest extends TestCase
{
    /**
     * @coversNothing
     */
    public function testUsage()
    {
        $sut = new DocBlock();

        $sut->setSummary('This is the summary for a DocBlock.');

        $sut->setDescription('This is the description for a DocBlock. This text may contain multiple lines and even some _markdown_.');
        $sut->addDescription('');
        $sut->addDescription('* Markdown style lists function too');
        $sut->addDescription('* Just try this out once');
        $sut->addDescription('');
        $sut->addDescription('The section after the description contains the tags; which provide structured meta-data concerning the given element.');

        $sut->addTag(new Author('Mike van Riel', 'me@mikevanriel.com'));

        $sut->addTag(new Since('1.0'));

        $sut->addTag(new Param('example', new Integer(), false, new Description('This is an example function/method parameter description.')));
        $sut->addTag(new Param('example2', new String_(), false, new Description('This is a second example.')));

        $expect = <<<'PHPDOC'
/**
 * This is the summary for a DocBlock.
 *
 * This is the description for a DocBlock. This text may contain
 * multiple lines and even some _markdown_.
 *
 * * Markdown style lists function too
 * * Just try this out once
 *
 * The section after the description contains the tags; which provide
 * structured meta-data concerning the given element.
 *
 * @author Mike van Riel <me@mikevanriel.com>
 *
 * @since 1.0
 *
 * @param int    $example  This is an example function/method parameter description.
 * @param string $example2 This is a second example.
 */
PHPDOC;

        $this->assertEquals($expect, (string) $sut);
    }
}
