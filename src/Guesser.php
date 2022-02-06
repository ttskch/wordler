<?php

declare(strict_types=1);

namespace Ttskch\Wordler;

final class Guesser
{
    public function __construct(private array $dictionary)
    {
    }

    public function guess(array $invalidWords = []): string
    {
        // @todo
        $candidates = array_values(array_filter($this->dictionary, static fn (string $word) => ! in_array($word, $invalidWords)));

        return $candidates[0];
    }
}
