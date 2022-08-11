<?php

declare(strict_types=1);

namespace Ttskch\Wordler;

use Ttskch\Wordler\Exception\NoMoreCandidatesException;

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

    public function guess(CandidateProvider $candidateProvider): string
    {
        $candidates = $candidateProvider->getCandidates();

        if (count($candidates) === 0) {
            throw new NoMoreCandidatesException();
        }

        $primary = [
            'word' => null,
            'score' => 0,
        ];

        foreach ($candidates as $candidate) {
            $score = 0;
            $usedCharacters = [];
            foreach (str_split($candidate) as $ch) {
                // prefer word with no overlapping characters to get more information from feedback
                if (!in_array($ch, $usedCharacters, true)) {
                    $score += $this->characterScores[$ch];
                    $usedCharacters[] = $ch;
                }
            }

            if ($score > $primary['score']) {
                $primary['word'] = $candidate;
                $primary['score'] = $score;
            }
        }

        return $primary['word'];
    }
}
