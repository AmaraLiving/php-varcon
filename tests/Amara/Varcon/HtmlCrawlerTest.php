<?php

namespace Amara\Varcon\Tests;

use Amara\Varcon\HtmlCrawler;

class HtmlCrawlerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideCrawlAndModifyWithChangedXpathExpressions
     *
     * @param string $html
     * @param string $changedHtml
     */
    public function testCrawlAndModifyWithChangedXpathExpressions($html, $changedHtml)
    {
        $htmlCrawler = new HtmlCrawler();
        $xpathExpressions = [
            '//span/@unsupported', // Asserts unsupported attributes also get translated
            '//strong/text()'
        ];

        $htmlCrawler->setXpathExpressions($xpathExpressions);

        $callable = function () {
            return 'Changed';
        };

        $this->assertSame(
            $changedHtml,
            $htmlCrawler->crawlAndModify($html, $callable)
        );
    }

    /**
     * @return array
     */
    public function provideCrawlAndModifyWithChangedXpathExpressions()
    {
        return [
            [
                $html = '<p>Text <strong>text</strong> text</p>',
                $changedHtml = '<p>Text <strong>Changed</strong> text</p>',
            ],
            [
                $html = '<span unsupported="Text">Text text text</span>',
                $changedHtml = '<span unsupported="Changed">Text text text</span>',
            ],
            [
                $html = '<span>Unchanged</span>',
                $changedHtml = '<span>Unchanged</span>',
            ],
        ];
    }
}
