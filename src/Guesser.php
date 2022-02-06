<?php

declare(strict_types=1);

namespace Ttskch\Wordler;

final class Guesser
{
    // @see https://en.wikipedia.org/wiki/Letter_frequency
    private array $characterScores = [
        'a' => 7.8,
        'b' => 2,
        'c' => 4,
        'd' => 3.8,
        'e' => 11,
        'f' => 1.4,
        'g' => 3,
        'h' => 2.3,
        'i' => 8.2,
        'j' => 0.21,
        'k' => 2.5,
        'l' => 5.3,
        'm' => 2.7,
        'n' => 7.2,
        'o' => 6.1,
        'p' => 2.8,
        'q' => 0.24,
        'r' => 7.3,
        's' => 8.7,
        't' => 6.7,
        'u' => 3.3,
        'v' => 1,
        'w' => 0.91,
        'x' => 0.27,
        'y' => 1.6,
        'z' => 0.44,
    ];

    public function __construct(private array $dictionary)
    {
    }

    public function guess(array $invalidWords = []): string
    {
        // @todo
        $candidates = array_values(array_filter($this->dictionary, static fn (string $word) => ! in_array($word, $invalidWords)));

        $primary = [
            'word' => null,
            'score' => 0,
        ];

        foreach ($candidates as $candidate) {
            $score = 0;
            foreach (str_split($candidate) as $ch) {
                $score += $this->characterScores[$ch];
            }

            if ($score > $primary['score']) {
                $primary['word'] = $candidate;
                $primary['score'] = $score;
            }
        }

        return $primary['word'];
    }
}
