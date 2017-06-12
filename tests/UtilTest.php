<?php

namespace Amara\Varcon\Tests;

use Amara\Varcon\Util;
use PHPUnit_Framework_TestCase;

/**
 * Tests edge-case exceptions where something is wrong with the file.
 * All other functionality is tested in the Translator test.
 *
 * @see TranslatorTest
 */
class UtilTest extends PHPUnit_Framework_TestCase
{
    /**
     * A A: absinthe / AV B: absinth | :1
     * ^-^- Having 2 equal variants should not make the word questionable
     */
    public function testReadlineNoExpandPrepend()
    {
        $util = new Util();

        $this->assertEquals([
            'A' => [
                0 => [
                    0 => 'absinthe',
                ],
                3 => [
                    0 => 'absinth',
                ],
                1 => [
                    0 => 'absinthe',
                ],
            ],
            'B' => [
                0 => [
                    0 => 'absinth',
                ],
            ],
        ], $util->readline_no_expand('A A: absinthe / AV B: absinth | :1'));
    }

    /**
     * A: absinthe / AV B: absinth | :1 | Too much vertical lines
     * ---------------------------------^- Extra vertical line
     */
    public function testReadlineNoExpandInvalidFormatException()
    {
        $util = new Util();

        $this->setExpectedException(\RuntimeException::class, 'Invalid format, there should be 1 vertical line at most');
        $util->readline_no_expand('A: absinthe / AV B: absinth | :1 | Too much vertical lines');
    }

    /**
     * A absinthe / AV B: absinth | :1
     * -^- Missing colon
     */
    public function testReadlineNoExpandBadEntryException()
    {
        $util = new Util();

        $this->setExpectedException(\RuntimeException::class, 'Bad entry: A absinthe');
        $util->readline_no_expand('A absinthe / AV B: absinth | :1');
    }

    /**
     * K: absinthe / AV B: absinth | :1
     * ^- No language for "K" exists
     */
    public function testReadlineNoExpandBadCategoryException()
    {
        $util = new Util();

        $this->setExpectedException(\RuntimeException::class, 'Bad category: K');
        $util->readline_no_expand('K: absinthe / AV B: absinth | :1');
    }

    /**
     * If the passed parameter evaluates to false, we expect null in return
     */
    public function testGetClusterReturnsNullOnEmptyString()
    {
        $util = new Util();

        $this->assertSame(null, $util->get_cluster(''));
    }

    /**
     * absinthe <verified> (level 50)
     * ^- Missing #
     */
    public function testGetClusterShouldStartWithHashException()
    {
        $util = new Util();

        $badCluster = 'absinthe <verified> (level 50)';

        $this->setExpectedException(\RuntimeException::class, sprintf(
            'Expected cluster to start with comment: %s',
            $badCluster
        ));
        $util->get_cluster($badCluster);
    }

    /**
     * #  <verified> (level 50)
     * -^- Missing word
     */
    public function testGetClusterCannotExtractHeadwordException()
    {
        $util = new Util();

        $badCluster = '#  <verified> (level 50)';

        $this->setExpectedException(\RuntimeException::class, sprintf(
            'Could not extract headword from cluster: %s',
            $badCluster
        ));
        $util->get_cluster($badCluster);
    }

    /**
     * # absinthe <verified> (level )
     * ----------------------------^- Missing level
     */
    public function testGetClusterCannotExtractLevelException()
    {
        $util = new Util();

        $badCluster = '# absinthe <verified> (level )';

        $this->setExpectedException(\RuntimeException::class, sprintf(
            'Could not extract level from cluster: %s',
            $badCluster
        ));
        $util->get_cluster($badCluster);
    }
}
