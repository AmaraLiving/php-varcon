<?php

namespace Amara\Varcon;

/**
 * A translator/converter for different variations of the English spellings
 */
class Translator
{
    const QUESTIONABLE_IGNORE = 0;
    const QUESTIONABLE_INCLUDE = 1;
    const QUESTIONABLE_MARK = 2;

    /**
     * @var TranslationProviderInterface
     */
    private $provider;

    /**
     * Translator constructor.
     *
     * @param TranslationProviderInterface|null $provider
     */
    public function __construct(TranslationProviderInterface $provider = null)
    {
        if (null === $provider) {
            $provider = new TranslationProvider;
        }

        $this->provider = $provider;
    }

    /**
     * Translate/convert a string to another spelling
     *
     * @param string $string
     * @param string $fromSpelling
     * @param string $toSpelling
     * @param int $questionable
     *
     * @return string
     */
    public function translate($string, $fromSpelling, $toSpelling, $questionable = self::QUESTIONABLE_IGNORE)
    {
        $trans = $this->provider->getTranslations($fromSpelling, $toSpelling);

        $words = preg_split('/(\'?[^A-Za-z\']+\'?)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);

        $return = [];

        foreach ($words as $w) {
            if (!isset($trans[$w])) {
                $return[] = $w;
                continue;
            }

            $translationCount = count($trans[$w]);

            if (1 === $translationCount) {
                $return[] = $trans[$w][0];
            } elseif ($translationCount > 1 && $questionable == self::QUESTIONABLE_INCLUDE) {
                $return[] = $trans[$w][0];
            } elseif ($translationCount > 1 && $questionable == self::QUESTIONABLE_MARK) {
                $return[] = '?'.implode('/', $trans[$w]).'?';
            } else {
                $return[] = $w;
            }
        }

        return implode('', $return);
    }
}
