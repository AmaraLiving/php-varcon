<?php

namespace Amara\Varcon;

/**
 * An interface ensuring any future translators implement the translate method in the expected way
 */
interface TranslatorInterface
{
    const QUESTIONABLE_IGNORE = 0;
    const QUESTIONABLE_INCLUDE = 1;
    const QUESTIONABLE_MARK = 2;

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
    public function translate($string, $fromSpelling, $toSpelling, $questionable = self::QUESTIONABLE_IGNORE);
}
