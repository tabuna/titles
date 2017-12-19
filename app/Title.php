<?php

namespace App;

class Title
{

    /**
     * Title for news
     *
     * @var string
     */
    public $title;

    /**
     * Count of click
     *
     * @var int
     */
    public $click;

    /**
     * Hash for word
     *
     * @var array
     */
    private $hash = [];

    /**
     * Count click for hash
     *
     * @var int
     */
    private $clicks = [];

    /**
     * Titles constructor.
     *
     * @param $title
     * @param $click
     */
    public function __construct(string $title, int $click = 1)
    {
        $this->title = $title;
        $this->click = $click;
    }

    /**
     * Train
     *
     * @return array
     */
    public function get()
    {
        $this->hashWords();

        return [
            'samples' => $this->hash,
            'targets' => $this->clicks,
        ];
    }

    /**
     * Hash each word separately
     */
    private function hashWords()
    {
        $words = explode(" ", $this->title);

        foreach ($words as $word) {
            array_push($this->hash, [crc32($word)]);
            array_push($this->clicks, $this->click);
        }
    }


}
