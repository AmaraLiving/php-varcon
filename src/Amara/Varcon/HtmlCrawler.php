<?php

namespace Amara\Varcon;

use DOMDocument;
use DOMNode;
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
        $document = new DOMDocument();
        @$document->loadHTML(mb_convert_encoding(
            sprintf('<div>%s</div>', $content), /** @see stripDoctypeHtmlBodyAndHeadElements */
            'HTML-ENTITIES',
            'UTF-8'
        ));
        $this->stripDoctypeHtmlBodyAndHeadElements($document);

        $xpath = new DOMXPath($document);

        $textNodes = $xpath->query(implode('|', $this->xpathExpressions));

        /** @var DOMNode $textNode */
        foreach ($textNodes as $textNode) {
            $textNode->nodeValue = $callable($textNode->nodeValue);
        }

        return $document->saveHTML($document->documentElement);
    }

    /**
     * This method is a short hack to avoid incompatibilities between different PHP and Libxml setups. It has the same
     * effect as passing the LIBXML_HTML_NOIMPLIED and LIBXML_HTML_NODEFDTD flags to loadHtml's options
     *
     * It works by, first of all, wrapping all of the contents in a div, and then extracting only them back to the
     * DOM document. This way, we can get rid of the Doctype and all tags so kindly inserted by loadHtml
     *
     * @param DOMDocument $document
     */
    private function stripDoctypeHtmlBodyAndHeadElements(DOMDocument $document)
    {
        // First step - extract the div wrapper from the document
        $container = $document->getElementsByTagName('div')->item(0);
        $container = $container->parentNode->removeChild($container);

        // Remove all document children
        while ($document->firstChild) {
            $document->removeChild($document->firstChild);
        }

        // Append the div wrapper's children as children of the document
        while ($container->firstChild) {
            $document->appendChild($container->firstChild);
        }
    }
}
