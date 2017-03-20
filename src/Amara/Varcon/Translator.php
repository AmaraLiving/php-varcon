<?php

namespace Amara\Varcon;

/**
 * A translator/converter for different variations of the English spellings
 */
class Translator implements TranslatorInterface
{
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
     * {@inheritdoc}
     */
    public function translate($string, $fromSpelling, $toSpelling, $questionable = self::QUESTIONABLE_IGNORE)
    {
        $trans = $this->provider->getTranslations($fromSpelling, $toSpelling);

        $words = preg_split('/(\'?[^A-Za-z\']+\'?)/', $string, null, PREG_SPLIT_DELIM_CAPTURE);

        $return = [];

        foreach ($words as $w) {
            $return[] = $this->translateWord($w, $trans, $questionable);
        }

        return implode('', $return);
    }

    /**
     * Translate the given word according to the given translations array
     * Having this in a separate function can help track single-word translations
     *
     * @param string $word
     * @param array $translations
     * @param int $questionable
     *
     * @return string
     */
    protected function translateWord($word, array $translations, $questionable = self::QUESTIONABLE_IGNORE)
    {
        $originalWord = $word;

        $ucFirst = $this->isFirstLetterUppercase($originalWord);
        $allCaps = $this->isWholeStringUppercase($originalWord);

        if ($ucFirst || $allCaps) {
            $word = strtolower($originalWord);
        }

        if (!isset($translations[$word])) {
            return $originalWord;
        }

        $translationCount = count($translations[$word]);

        if ($ucFirst) {
            $translations[$word] = array_map('ucfirst', $translations[$word]);
        }

        if ($allCaps) {
            $translations[$word] = array_map('strtoupper', $translations[$word]);
        }

        if (1 === $translationCount) {
            return $translations[$word][0];
        } elseif ($translationCount > 1 && $questionable === self::QUESTIONABLE_INCLUDE) {
            return $translations[$word][0];
        } elseif ($translationCount > 1 && $questionable === self::QUESTIONABLE_MARK) {
            return '?'.implode('/', $translations[$word]).'?';
        }

        return $originalWord;
    }

    /**
     * Is The First Letter Capital
     *
     * @param string $string
     *
     * @return bool
     */
    private function isFirstLetterUppercase($string)
    {
        return ucfirst(strtolower($string)) === $string;
    }

    /**
     * IS EVERYTHING IN CAPITAL LETTERS
     *
     * @param string $string
     *
     * @return bool
     */
    private function isWholeStringUppercase($string)
    {
        return strtoupper($string) === $string;
    }
}
