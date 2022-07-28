<?php

namespace Amara\Varcon;

use PHPUnit\Framework\TestCase;

class TranslationProviderTest extends TestCase
{
    public function testGetTranslationsFileNotFoundException()
    {
        $translationProvider = new TranslationProvider('random-name-2439857.txt');

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('File not found: ');
        $translationProvider->getTranslations('B', 'A');
    }
}
