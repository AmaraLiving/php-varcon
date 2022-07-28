<?php

namespace Amara\Varcon\Tests;

use Amara\Varcon\HtmlTranslator;
use Amara\Varcon\Translator;
use PHPUnit\Framework\TestCase;
use Prophecy\Argument;

class HtmlTranslatorTest extends TestCase
{
    public function testTranslatePreservesWhitespace()
    {
        $htmlTranslator = new HtmlTranslator();

        $this->assertSame(
            '<p>Colour <strong>pyjama</strong> &#1087;&#1072;&#1088;&#1072;&#1083;&#1077;&#1083;&#1077;&#1087;&#1080;&#1087;&#1077;&#1076;</p>', // Tests UTF8 characters as well
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
            Argument::type('string'),
            'A',
            'B',
            0
        )->will(function ($arguments) {
            $string = array_shift($arguments);

            return str_replace(
                ['Text', 'text'],
                ['Translated', 'translated'],
                $string
            );
        });

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
                $html = '<p>Text & text text</p><p>More text</p><p>More text</p>',
                $translatedHtml = '<p>Translated &amp; translated translated</p><p>More translated</p><p>More translated</p>',
            ],
            [
                $html = '<p>&bull; Text &amp; <strong>text</strong>: text</p>',
                $translatedHtml = '<p>&bull; Translated &amp; <strong>translated</strong>: translated</p>',
            ],
            [
                $html = '<img src="#" alt="Text text text">',
                $translatedHtml = '<img src="#" alt="Translated translated translated">',
            ],
            [
                $html = '<img src="#" title="Text text text">',
                $translatedHtml = '<img src="#" title="Translated translated translated">',
            ],
            [
                $html = '<meta name="description" content="Text text text">',
                $translatedHtml = '<meta name="description" content="Translated translated translated">',
            ],
        ];
    }
}
