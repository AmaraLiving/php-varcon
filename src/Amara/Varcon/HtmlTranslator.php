<?php

namespace Amara\Varcon;

/**
 * Glues the HtmlCrawler with a Translator
 */
class HtmlTranslator implements TranslatorInterface
{
    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var HtmlCrawler
     */
    private $htmlCrawler;

    /**
     * @param TranslatorInterface $translator
     * @param HtmlCrawler $htmlCrawler
     */
    public function __construct(TranslatorInterface $translator = null, HtmlCrawler $htmlCrawler = null)
    {
        if (null === $translator) {
            $translator = new Translator;
        }
        if (null === $htmlCrawler) {
            $htmlCrawler = new HtmlCrawler;
        }

        $this->translator = $translator;
        $this->htmlCrawler = $htmlCrawler;
    }

    /**
     * {@inheritdoc}
     */
    public function translate($htmlContent, $fromSpelling, $toSpelling, $questionable = self::QUESTIONABLE_IGNORE)
    {
        $callable = function ($extractedString) use ($fromSpelling, $toSpelling, $questionable) {
            return $this->translator->translate($extractedString, $fromSpelling, $toSpelling, $questionable);
        };

        return $this->htmlCrawler->crawlAndModify($htmlContent, $callable);
    }
}
