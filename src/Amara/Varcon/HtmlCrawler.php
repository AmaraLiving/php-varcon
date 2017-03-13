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
        $document = new DOMDocument();
        $document->loadHTML(mb_convert_encoding(
            sprintf('<div>%s</div>', $content),
            'HTML-ENTITIES',
            'UTF-8'
        ));
        $this->stripDoctypeHtmlBodyAndHeadElements($document);

        $xpath = new DOMXPath($document);

        $textNodes = $xpath->query(implode('|', $this->xpathExpressions));

        /** @var DOMText $textNode */
        foreach ($textNodes as $textNode) {
            $textNode->nodeValue = $callable($textNode->nodeValue);
        }

        return $document->saveHTML($document->documentElement);
    }

    /**
     * This method is a short hack to avoid incompatibilities between different PHP and Libxml setups. It has the same
     * effect as passing the LIBXML_HTML_NOIMPLIED and LIBXML_HTML_NODEFDTD flags to loadHtml's options
     *
     * @param DOMDocument $document
     */
    private function stripDoctypeHtmlBodyAndHeadElements(DOMDocument $document)
    {
        $container = $document->getElementsByTagName('div')->item(0);
        $container = $container->parentNode->removeChild($container);

        while ($document->firstChild) {
            $document->removeChild($document->firstChild);
        }

        while ($container->firstChild) {
            $document->appendChild($container->firstChild);
        }
    }
}
