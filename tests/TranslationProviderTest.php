<?php

namespace Amara\Varcon;

use PHPUnit_Framework_TestCase;

class TranslationProviderTest extends PHPUnit_Framework_TestCase
{
    public function testGetTranslationsFileNotFoundException()
    {
        $translationProvider = new TranslationProvider('random-name-2439857.txt');

        $this->setExpectedException(\RuntimeException::class, 'File not found: ');
        $translationProvider->getTranslations('B', 'A');
    }
}
