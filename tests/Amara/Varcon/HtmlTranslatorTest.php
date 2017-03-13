<?php

namespace Amara\Varcon\Tests;

use Amara\Varcon\HtmlTranslator;
use Amara\Varcon\Translator;
use Prophecy\Argument;

class HtmlTranslatorTest extends \PHPUnit_Framework_TestCase
{
    public function testTranslatePreservesWhitespace()
    {
        $htmlTranslator = new HtmlTranslator();

        $this->assertSame(
            '<p>Colour <strong>pyjama</strong> паралелепипед</p>', // Tests UTF8 characters as well
            $htmlTranslator->translate(
                '<p>Color <strong>pajama</strong> паралелепипед</p>',
                'A',
                'B'
            )
        );
    }

    /**
     * @dataProvider provideTranslate
     *
     * @param string $html
     * @param string $translatedHtml
     */
    public function testTranslate($html, $translatedHtml)
    {
        $translator = $this->prophesize(Translator::class);
        $translator->translate(
            Argument::any(),
            Argument::any(),
            Argument::any(),
            Argument::any()
        )->willReturn('Translated'); // Keep in mind we ignore whitespace this way

        $htmlTranslator = new HtmlTranslator($translator->reveal());

        $this->assertSame($translatedHtml, $htmlTranslator->translate($html, 'A', 'B'));
    }

    /**
     * @return array
     */
    public function provideTranslate()
    {
        return [
            [
                $html = '<p>Text text text</p>',
                $translatedHtml = '<p>Translated</p>',
            ],
            [
                $html = '<p>Text <strong>text</strong> text</p>',
                $translatedHtml = '<p>Translated<strong>Translated</strong>Translated</p>',
            ],
            [
                $html = '<img src="#" alt="Text text text">',
                $translatedHtml = '<img src="#" alt="Translated">',
            ],
            [
                $html = '<img src="#" title="Text text text">',
                $translatedHtml = '<img src="#" title="Translated">',
            ],
            [
                $html = '<meta name="description" content="Text text text">',
                $translatedHtml = '<meta name="description" content="Translated">',
            ],
        ];
    }
}
