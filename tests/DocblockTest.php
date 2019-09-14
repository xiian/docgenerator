<?php
declare(strict_types=1);

namespace xiian\docgenerator\test;

use phpDocumentor\Reflection\DocBlock\Description;
use phpDocumentor\Reflection\DocBlock\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\{Author, Param, Since};
use phpDocumentor\Reflection\Types\{Integer, String_};
use PHPUnit\Framework\TestCase;
use xiian\docgenerator\DocBlock;

/**
 * @coversDefaultClass \xiian\docgenerator\DocBlock
 * @uses \xiian\docgenerator\DocBlock::__construct
 * @uses \xiian\docgenerator\TagsCollection
 */
class DocblockTest extends TestCase
{
    /** @var DocBlock */
    private $sut;

    protected function setUp(): void
    {
        $this->sut = new DocBlock();
    }

    /**
     * @covers ::addDescription
     * @uses \xiian\docgenerator\DocBlock::getDescription
     * @uses \xiian\docgenerator\DocBlock::setDescription
     */
    public function testAddDescriptionWithExisting()
    {
        $input = 'else';

        $existing = 'before';
        $expect   = $existing . PHP_EOL . $input;

        $this->sut->setDescription($existing);
        $out = $this->sut->addDescription($input);
        $this->assertSame($this->sut, $out, 'addDescription should return self');
        $this->assertEquals($expect, $this->sut->getDescription()->render());
    }

    /**
     * @covers ::addDescription
     * @uses \xiian\docgenerator\DocBlock::getDescription
     */
    public function testAddDescriptionWithUninitialized()
    {
        $input  = 'else';
        $expect = PHP_EOL . $input;

        $out = $this->sut->addDescription($input);
        $this->assertSame($this->sut, $out, 'addDescription should return self');
        $this->assertEquals($expect, $this->sut->getDescription()->render());
    }

    /**
     * @covers ::addTag
     * @uses \xiian\docgenerator\DocBlock::getTags
     */
    public function testAddTag()
    {
        $this->assertCount(0, $this->sut->getTags(), 'There should not be any tags on initialization of DocBlock');

        $tag = $this->createMockTag();
        $out = $this->sut->addTag($tag);
        $this->assertSame($this->sut, $out, 'addTag should return self');

        $tags = $this->sut->getTags();
        $this->assertCount(1, $tags, 'Should have a single tag');
        $this->assertContains($tag, $tags, 'Tag added should be included in collection');
    }

    private function createMockTag(): Tag
    {
        return $this->getMockBuilder(Tag::class)->getMock();
    }

    /**
     * @covers ::__construct
     */
    public function testConstruction()
    {
        $sut = new DocBlock();
        $this->assertInstanceOf(DocBlock::class, $sut);
    }

    /**
     * @covers ::getDescription
     */
    public function testGetDescriptionFresh()
    {
        $out = $this->sut->getDescription();

        $this->assertInstanceOf(Description::class, $out);
        $this->assertEmpty($out->render(), 'Description should be empty initially');
        $this->assertEmpty($out->getTags(), 'Description should not contain any tags');
    }

    /**
     * @covers ::getDescription
     */
    public function testGetDescriptionReturnsSame()
    {
        $this->assertSame($this->sut->getDescription(), $this->sut->getDescription(), 'Get description should return same object on consecutive calls');
    }

    /**
     * @covers ::getSummary
     * @uses \xiian\docgenerator\DocBlock::setSummary
     */
    public function testGetSummaryReturnsSummarySet()
    {
        $summary = 'random summary ' . __METHOD__;
        $this->sut->setSummary($summary);

        $this->assertEquals($summary, $this->sut->getSummary(), 'Summary should be as set');
    }

    /**
     * @covers ::getSummary
     */
    public function testGetSummaryUninitialized()
    {
        $this->assertNull($this->sut->getSummary(), 'Summary should be null if uninitialized');
    }

    /**
     * @covers ::getTags
     */
    public function testGetTagsUninitialized()
    {
        $this->assertEmpty($this->sut->getTags());
    }

    /**
     * @covers ::getTags
     * @uses \xiian\docgenerator\DocBlock::addTag
     */
    public function testGetTagsWithContents()
    {
        $tag1 = $this->createMockTag();
        $tag2 = $this->createMockTag();

        $this->sut->addTag($tag1);
        $this->sut->addTag($tag2);

        $expect = [$tag1, $tag2];

        $out = $this->sut->getTags();
        $this->assertCount(2, $out, 'Tags collection should have correct number of Tags');
        $this->assertEqualsCanonicalizing($expect, $out, 'Tags collection should match the expected tags provided');
    }

    /**
     * @covers ::setDescription
     * @uses \xiian\docgenerator\DocBlock::getDescription
     */
    public function testSetDescription()
    {
        $input = 'something';
        $out   = $this->sut->setDescription($input);
        $this->assertSame($this->sut, $out, 'setDescription should return self');
        $this->assertEquals($input, $this->sut->getDescription()->render(), 'Setting description should set the description');
        return $this->sut;
    }

    /**
     * @covers ::setDescription
     * @uses    \xiian\docgenerator\DocBlock::getDescription
     * @depends testSetDescription
     */
    public function testSetDescriptionOverridesOld(DocBlock $sut)
    {
        $input = 'other';
        $out   = $sut->setDescription($input);
        $this->assertSame($sut, $out, 'setDescription should return self');
        $this->assertEquals($input, $sut->getDescription()->render(), 'Setting description should override any previous description');
    }

    /**
     * @covers ::setSummary
     * @uses \xiian\docgenerator\DocBlock::getSummary
     */
    public function testSetSummaryShouldRemoveSummary()
    {
        $summary = 'random summary ' . __METHOD__;
        $this->sut->setSummary($summary);
        $this->assertEquals($summary, $this->sut->getSummary(), 'Summary should be as set');

        $out = $this->sut->setSummary(null);
        $this->assertSame($this->sut, $out, 'setSumary should return self');

        $this->assertNull($this->sut->getSummary(), 'Summary should be null');
    }

    /**
     * @covers ::setSummary
     * @uses \xiian\docgenerator\DocBlock::getSummary
     */
    public function testSetSummaryShouldStick()
    {
        $summary = 'random summary ' . __METHOD__;
        $out     = $this->sut->setSummary($summary);
        $this->assertSame($this->sut, $out, 'setSumary should return self');

        $this->assertEquals($summary, $this->sut->getSummary(), 'Summary should be as set');
    }

    /**
     * @coversNothing
     */
    public function testUsage()
    {
        $this->sut->setSummary('This is the summary for a DocBlock.');

        $this->sut->setDescription('This is the description for a DocBlock. This text may contain multiple lines and even some _markdown_.');
        $this->sut->addDescription('');
        $this->sut->addDescription('* Markdown style lists function too');
        $this->sut->addDescription('* Just try this out once');
        $this->sut->addDescription('');
        $this->sut->addDescription('The section after the description contains the tags; which provide structured meta-data concerning the given element.');

        $this->sut->addTag(new Author('Mike van Riel', 'me@mikevanriel.com'));

        $this->sut->addTag(new Since('1.0'));

        $this->sut->addTag(new Param('example', new Integer(), false, new Description('This is an example function/method parameter description.')));
        $this->sut->addTag(new Param('example2', new String_(), false, new Description('This is a second example.')));

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

        $this->assertEquals($expect, (string) ($this->sut));
    }
}
