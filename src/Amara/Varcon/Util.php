<?php

namespace Amara\Varcon;

/**
 * Converts word clusters and lines from a varcon file into manageable formats
 *
 * @todo Tidy the whole thing when safe to do so
 */
class Util
{
    const MAX_VARIANT_LEVEL = 9;

    /**
     * @var array
     */
    public static $map = [
        // American spelling
        'A' => 'american',
        // British "ise" spelling
        'B' => 'british',
        // British "ize" spelling or OED preferred Spelling
        'Z' => 'british_z',
        // Canadian spelling
        'C' => 'canadian',
        // Australian spelling
        'D' => 'australian',
        // Other - see $vmap (Variant info based on American dictionaries, never used with any of the above)
        '_' => 'other',
    ];

    /**
     * Variation map
     *
     * @var array
     */
    protected $vmap = [
        '' =>  -1, // ?
        '.' => 0, // equal
        'v' => 1, // variant
        'V' => 2, // seldom used variant
        '-' => 3, // possible variant, should generally not be used
        'x' => 8, // improper variant (should not use)

        '0' => 0,
        '1' => 1,
        '2' => 2,
        '3' => 3,
        '8' => 8,
    ];

    /**
     * Convert a varcon line into a processable array
     *
     * Possible inputs for $_
     * A Z: abolitionized / B: abolitionised
     * A: absinthe / AV B: absinth | :1
     * A B: absinthe | :2
     *
     * @param string $_
     * @param mixed $n  This variable is only used in the varcon split command, so might be unnecessary
     *
     * @return array
     */
    public function readline_no_expand($_, $n = null)
    {
        $_ = trim($_, PHP_EOL); // A: absinthe / AV B: absinth | :1

        $dn = explode(' | ', $_); // [ 'A: absinthe / AV B: absinth', ':1' ]

        if (count($dn) > 2) {
            throw new \RuntimeException('Invalid format, there should be 1 horizontal line at most');
        }

        $d = explode(' / ', $dn[0]); // [ 0 => 'A: absinthe', 1 => 'AV B: absinth' ]

        $fragile = false;

        // The keys of $r will be spellings - A, B, Z, etc - see $this->map
        $r = [];
        foreach ($d as $__) {
            $matched = preg_match('/^(.+?): (.+)$/', $__, $sw); // Split by ": "
            if ($matched !== 1) {
                throw new \RuntimeException(sprintf("Bad entry: %s", $__));
            }

            list(
                $sw, // CategoryWord / SpellingWord // AV B: absinth
                $c, // Category // AV B
                $w // Word // absinth
            ) = $sw; // [ 'AV B: absinth', 'AV B', 'absinth' ]

            $c = explode(' ', $c); // [ 'AV', 'B' ]
            foreach ($c as $___) {
                // Match (spelling)(variation)(number) - there is usually never a number
                $matched = preg_match('/^([ABZCD_*Q])([.01234vVx-]?)(\d)?$/', $___, $sVNum);
                if (1 !== $matched) {
                    throw new \RuntimeException(sprintf('Bad category: %s', $___));
                }

                // But if there IS a number
                if (isset($sVNum[3])) {
                    $fragile = true;
                }

                list(
                    $sVNum, // AV
                    $s, // A
                    $v // V
                ) = $sVNum; // [ 'AV', 'A', 'V' ]

                // $r['A']
                if (!isset($r[$s]))  {
                    $r[$s] = [];
                }

                // $this->vmap['V'] = 2 + 1
                $variationNumber = $this->vmap[$v] + 1;

                // $r['A'][3]
                if (!isset($r[$s][$variationNumber])) {
                    $r[$s][$variationNumber] = [];
                }

                // $r['A'][3][] = 'absinth'
                array_push($r[$s][$variationNumber], $w);
            }
        }

        foreach ($r as &$read) {
            if (isset($read[0]) && count($read[0]) > 1) {
                // Prepends all except the first element - from [0] to [1] = perl unshift with splice 1
                $read[1] = array_merge(array_splice($read[0], 1), $read[1]);
            }
        }

        if (null !== $n) {
            if ($fragile) {
                $n->fragile = true;
                $n->orig_data = $dn[0];
            }

            // Most thorough note example: (-) <N> journalist

            $__ = $dn[1];
            $n->_ = $__;
            // Looks for (-) in the note section, meaning an uncommon usage
            if (1 === preg_match('/^ *\(-\)/', $__)) {
                $n->uncommon = true;

                $__ = trim($__);

                // Gets rid of the (-)
                $__ = substr($__, 3);
            }

            // Looks for <POS> in the note section, denoting a speech/sentence part (I think)
            // <N> should mean a noun
            // <Adj> should mean an adjective
            // etc...
            if (1 === preg_match('/^ *<(.+?)>/', $__, $pos)) {
                $n->pos = $pos[1];

                $__ = trim($__);

                // Gets rid of the <POS>
                $__ = substr($__, strlen($pos[1]));
            }

            $__ = trim($__);

            $n->note = $__;
        }

        return $r;
    }

    /**
     * Read a line and fill in the gaps
     *
     * @param string $_
     * @param object|null $n
     *
     * @return array
     */
    public function readline($_, $n = null)
    {
        $r = $this->readline_no_expand($_, $n);

        // If there is no British "ize", use good ole classic British
        if (isset($r['B']) && !isset($r['Z'])) {
            $r['Z'] = $r['B'];
        }

        // If there is no Canadian, use British "ize" as the closest replacement
        if (isset($r['Z']) && !isset($r['C'])) {
            $r['C'] = $r['Z'];
        }

        // If we have no Australian, use British as the closest replacement
        if (isset($r['B']) && !isset($r['D'])) {
            $r['D'] = $r['B'];
        }

        return $r;
    }

    /**
     * Flatten a translation array
     *
     * @param array $p
     *
     * @return array
     */
    public function flatten(array $p)
    {
        $r = [];

        foreach ($p as $key => $pItem) {
            for ($v = -1; $v < static::MAX_VARIANT_LEVEL; $v++) {
                if (!isset($pItem[$v+1])) {
                    continue;
                }

                $vs = ($v == -1 ? '' : $v);
                $r[$key.$vs] = [ $p[$key][$v+1] ];
            }
        }

        return $r;
    }

    /**
     * Reverses a flattened array
     *
     * @param array $flattened
     *
     * @return array
     */
    public function reverse(array $flattened)
    {
        $r = [];

        foreach ($flattened as $tag => $words) {
            foreach ($words as $word) {
                if (!isset($r[$word])) {
                    $r[$word] = [];
                }

                array_push($r[$word], $tag);
            }
        }

        return $r;
    }

    /**
     * Should the given line should not be processed
     *
     * @param $_
     *
     * @return bool
     */
    public function filter($_)
    {
        // Replace with everything up to the first hash-sign or newline
        $_ = preg_replace("/^([^#\n]*)(.*)/", '$1', $_);
        $_ = trim($_);

        return empty($_);
    }

    /**
     * Extract data from a multi-line cluster
     *
     * @param string $_
     *
     * @return array|null
     */
    public function get_cluster($_)
    {
        if (!$_) {
            return null;
        }

        if (0 !== strpos($_, '#')) {
            throw new \RuntimeException(sprintf('Expected cluster to start with comment: %s', $_));
        }

        preg_match('/^# +([[:alpha:]_\'-]+)/', $_, $headword);

        if (!isset($headword[1])) {
            throw new \RuntimeException(sprintf('Could not extract headword from cluster: %s', $_));
        }
        $headword = $headword[1];

        preg_match('/^\# .+ \(level (\d\d)\)/m', $_, $level);

        if (!isset($level[1])) {
            throw new \RuntimeException(sprintf('Could not extract level from cluster: %s', $_));
        }
        $level = $level[1];

        return [
            'headword' => $headword,
            'level' => $level,
            'data' => $_,
        ];
    }
}
