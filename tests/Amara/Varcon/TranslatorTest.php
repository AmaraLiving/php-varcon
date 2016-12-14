<?php

namespace Amara\Varcon\Tests;

use Amara\Varcon\Translator;
use PHPUnit_Framework_TestCase;

class TranslatorTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Translator
     */
    private $translator;

    /**
     * {@inheritdoc}
     */
    public function setUp()
    {
        $this->translator = new Translator();

        parent::setUp();
    }

    /**
     * @return array
     */
    public function translateDataProvider()
    {
        return [
            [
                $questionable = Translator::QUESTIONABLE_IGNORE,
                $british = 'My pyjama\'s colour is not as greyish as it looks',
                $american = 'My pajama\'s color is not as grayish as it looks',
                $canadian = 'My pyjama\'s colour is not as greyish as it looks',
                $australian = 'My pyjama\'s colour is not as greyish as it looks',
                $british_z = 'My pyjama\'s colour is not as greyish as it looks',
                $variation = null,
            ],
            [
                $questionable = Translator::QUESTIONABLE_IGNORE,
                $british = 'The 50x50centimetres-cabinet is finally finalised',
                $american = 'The 50x50centimeters-cabinet is finally finalized',
                $canadian = 'The 50x50centimetres-cabinet is finally finalized',
                $australian = 'The 50x50centimetres-cabinet is finally finalised',
                $british_z = 'The 50x50centimetres-cabinet is finally finalized',
                $variation = null,
            ],
            [
                $questionable = Translator::QUESTIONABLE_IGNORE,
                $british = 'No uncommon words here',
                $american = 'No uncommon words here',
                $canadian = 'No uncommon words here',
                $australian = 'No uncommon words here',
                $british_z = 'No uncommon words here',
                $variation = 'No uncommon words here',
            ],
            [
                $questionable = Translator::QUESTIONABLE_INCLUDE,
                $british = ['One metre', 'One meter'],
                $american = 'One meter',
                $canadian = ['One metre', 'One meter'],
                $australian = ['One metre', 'One meter'],
                $british_z = ['One metre', 'One meter'],
                $variation = null,
            ],
            [
                $questionable = Translator::QUESTIONABLE_INCLUDE,
                $british = ['adviser', 'advisor'],
                $american = ['adviser', 'advisor'],
                $canadian = ['adviser', 'advisor'],
                $australian = ['adviser', 'advisor'],
                $british_z = ['adviser', 'advisor'],
                $variation = ['adviser', 'advisor'],
            ],
            [
                $questionable = Translator::QUESTIONABLE_MARK,
                $british = ['One metre high', 'One ?meter/metre? high'],
                $american = 'One meter high',
                $canadian = ['One metre high', 'One ?meter/metre? high'],
                $australian = ['One metre high', 'One ?meter/metre? high'],
                $british_z = ['One metre high', 'One ?meter/metre? high'],
                $variation = null,
            ],
        ];
    }

    /**
     * Test all possible variations of the translate function
     *
     * @dataProvider translateDataProvider
     *
     * @param int $questionable
     * @param string|array $british
     * @param string|array $american
     * @param string|array $canadian
     * @param string|array $australian
     * @param string|array $british_z
     * @param string|array|null $variation
     */
    public function testTranslate($questionable, $british, $american, $canadian, $australian, $british_z, $variation)
    {
        $crossTestStrings = [
            'B' => $british,
            'A' => $american,
            'C' => $canadian,
            'D' => $australian,
            'Z' => $british_z,
            '-' => $variation,
        ];

        foreach ($crossTestStrings as $from => $preTranslatedString) {
            if (null === $preTranslatedString) {
                continue;
            }

            if (is_array($preTranslatedString)) {
                $preTranslatedString = $preTranslatedString[0];
            }

            foreach ($crossTestStrings as $to => $expected) {
                if (null === $expected || $from == $to) {
                    continue;
                }

                if (is_array($expected)) {
                    if ($expected[0] == $preTranslatedString) {
                        $expected = $expected[0];
                    } else {
                        $expected = $expected[1];
                    }
                }

                $this->assertSame(
                    $expected,
                    $this->translator->translate($preTranslatedString, $from, $to, $questionable),
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
