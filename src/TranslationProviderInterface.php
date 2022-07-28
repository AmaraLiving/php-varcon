<?php

namespace Amara\Varcon;

/**
 * An interface for translation providers
 */
interface TranslationProviderInterface
{
    /**
     * Load translations for the given from-to conversion
     *
     * @param string $from
     * @param string $to
     * @param int $threshold
     *
     * @return array
     */
    public function getTranslations($from, $to, $threshold = 80);
}
