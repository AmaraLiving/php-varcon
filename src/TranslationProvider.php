<?php

namespace Amara\Varcon;

/**
 * A provider for the translator
 */
class TranslationProvider implements TranslationProviderInterface
{
    /**
     * @var string
     */
    private $filePath;

    /**
     * @var Util
     */
    private $util;

    /**
     * TranslationProvider constructor.
     *
     * @param string|null $filePath
     * @param Util|null $util
     */
    public function __construct($filePath = null, Util $util = null)
    {
        if (null === $filePath) {
            $filePath = __DIR__ . '/../resources/varcon.txt';
        }
        if (null === $util) {
            $util = new Util();
        }

        $this->filePath = $filePath;
        $this->util = $util;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations($from, $to, $threshold = 80)
    {
        if (!file_exists($this->filePath)) {
            throw new \RuntimeException(sprintf('File not found: %s', $this->filePath));
        }

        $fileHandle = fopen($this->filePath, 'r');

        $trans = [];

        while (false !== ($line = stream_get_line($fileHandle, PHP_INT_MAX, PHP_EOL.PHP_EOL))) {
            $d = $this->util->get_cluster($line);

            if ($d['level'] > $threshold) {
                continue;
            }

            $lines = explode(PHP_EOL, $d['data']);

            foreach ($lines as $line) {
                if ($this->util->filter($line)) {
                    continue;
                }

                $r = $this->util->readline($line);

                $froms = [ $from ];
                if ($from == '-') {
                    $froms = Util::$map;
                    unset($froms[$to]);

                    $froms = array_keys($froms);
                }

                foreach ($froms as $f) {
                    foreach (range(0, 3) as $v) {
                        if (!isset($r[$f][$v])) {
                            continue;
                        }

                        foreach ($r[$f][$v] as $_) {
                            if (!isset($trans[$_])) {
                                $trans[$_] = [];
                            }

                            if (isset($r[$to])) {
                                array_push($trans[$_], $r[$to][0][0]);
                            }
                        }
                    }
                }
            }
        }

        fclose($fileHandle);

        return $trans;
    }
}
