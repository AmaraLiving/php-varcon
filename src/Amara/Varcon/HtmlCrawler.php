<?php

namespace Amara\Varcon;

use DOMDocument;
use DOMText;
use DOMXPath;

/**
 * An extractor of HTML text nodes as well as attributes, which are known to be important for SEO
 */
class HtmlCrawler
{
    /**
     * @var array
     */
    private $xpathExpressions = [
        '//text()',
        '//img/@alt',
        '//img/@title',
        '//meta[@name="description"]/@content',
    ];

    /**
     * @param array $xpathExpressions
     */
    public function setXpathExpressions(array $xpathExpressions)
    {
        $this->xpathExpressions = $xpathExpressions;
    }

    /**
     * Crawl the HTML content provided and apply a callable (usually a wrapper of the Translator's translate function)
     *
     * @param string $content
     * @param callable $callable
     *
     * @return string
     */
    public function crawlAndModify($content, callable $callable)
    {
        $dom = new DOMDocument();
        $dom->loadHTML($content, LIBXML_HTML_NOIMPLIED|LIBXML_HTML_NODEFDTD);

        $xpath = new DOMXPath($dom);

        $textNodes = $xpath->query(implode('|', $this->xpathExpressions));

        /** @var DOMText $textNode */
        foreach ($textNodes as $textNode) {
            $textNode->nodeValue = $callable($textNode->nodeValue);
        }

        return $dom->saveHTML($dom->documentElement);
    }
}
