<?php

namespace Amara\Varcon\Tests;

use Amara\Varcon\Translator;
use PHPUnit_Framework_TestCase;

class VarconTranslatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @return array
     */
    public function translateDataProvider()
    {
        return [
            [
                $british = 'My pyjama\'s colour is not as greyish as it looks',
                $american = 'My pajama\'s color is not as grayish as it looks',
                $canadian = 'My pyjama\'s colour is not as greyish as it looks',
                $australian = 'My pyjama\'s colour is not as greyish as it looks',
                $british_z = 'My pyjama\'s colour is not as greyish as it looks',
            ],
            [
                $british = 'The 50x50centimetres-cabinet is finally finalised',
                $american = 'The 50x50centimeters-cabinet is finally finalized',
                $canadian = 'The 50x50centimetres-cabinet is finally finalized',
                $australian = 'The 50x50centimetres-cabinet is finally finalised',
                $british_z = 'The 50x50centimetres-cabinet is finally finalized',
            ],
        ];
    }

    /**
     * Test all possible variations of the translate function
     *
     * @dataProvider translateDataProvider
     *
     * @param string $british
     * @param string $american
     * @param string $canadian
     * @param string $australian
     * @param string $british_z
     */
    public function testTranslate($british, $american, $canadian, $australian, $british_z)
    {
        $translator = new Translator();

        $crossTestStrings = [
            'B' => $british,
            'A' => $american,
            'C' => $canadian,
            'D' => $australian,
            'Z' => $british_z,
        ];

        foreach ($crossTestStrings as $from => $preTranslatedString) {
            foreach ($crossTestStrings as $to => $expected) {
                $this->assertSame(
                    $expected,
                    $translator->translate($preTranslatedString, $from, $to),
                    sprintf('Unexpected result on %s to %s translation: %s',
                        $from,
                        $to,
                        $preTranslatedString
                    )
                );
            }
        }
    }
}
