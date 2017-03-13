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
                $html = '<p>Text & text text</p>',
                $translatedHtml = '<p>Translated &amp; translated translated</p>',
            ],
            [
                // Some day, this will stay as &bull; ..some ..day
                $html = '<p>&bull; Text &amp; <strong>text</strong>: text</p>',
                $translatedHtml = '<p>• Translated &amp; <strong>translated</strong>: translated</p>',
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
