<?php

declare(strict_types=1);

namespace Ttskch\Wordler;

final class CandidateProvider
{
    private array $candidates;

    public function __construct(array $dictionary)
    {
        $this->candidates = $dictionary;
    }

    public function getCandidates(): array
    {
        return $this->candidates;
    }

    public function addInvalidWord(string $invalidWord): void
    {
        $this->candidates = array_values(array_filter($this->candidates, fn (string $word) => $word !== $invalidWord));
    }

    public function addHistory(string $word, array $states): void
    {
        $characters = str_split($word);

        // remove unmatched candidates
        foreach ($this->candidates as $i => $candidate) {
            $candidateCharacters = str_split($candidate);

            for ($j = 0; $j < 5; $j++) {
                if ($states[$j] === Wordler::STATE_CORRECT) {
                    if ($characters[$j] !== $candidateCharacters[$j]) {
                        unset($this->candidates[$i]);
                        break;
                    }
                } elseif ($states[$j] === Wordler::STATE_PRESENT) {
                    if ($characters[$j] === $candidateCharacters[$j] || !in_array($characters[$j], $candidateCharacters, true)) {
                        unset($this->candidates[$i]);
                        break;
                    }
                } else { // absent
                    if (in_array($characters[$j], $candidateCharacters, true)) {
                        // even if absent, it's allowed to exist "correct" or "present" position
                        for ($k = 0; $k < 5; $k++) {
                            if (in_array($states[$k], [Wordler::STATE_CORRECT, Wordler::STATE_PRESENT]) && $characters[$k] === $characters[$j]) {
                                continue 2;
                            }
                        }
                        unset($this->candidates[$i]);
                        break;
                    }
                }
            }
        }

        $this->candidates = array_values($this->candidates);
    }
}
