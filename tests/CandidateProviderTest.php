<?php

declare(strict_types=1);

namespace Ttskch\Wordler;

use PHPUnit\Framework\TestCase;

class CandidateProviderTest extends TestCase
{
    /**
     * @dataProvider addHistoryDataProvider
     */
    public function testAddHistory(array $candidates, string $word, array $states, array $restCandidates): void
    {
        $SUT = new CandidateProvider($candidates);
        $SUT->addHistory($word, $states);
        $this->assertEquals($restCandidates, $SUT->getCandidates());
    }

    public function addHistoryDataProvider(): array
    {
        return [
            [
                [
                    'skill',
                    'skimp',
                    'skint',
                    'skirl',
                    'skirt',
                    'skive',
                    'stair',
                    'swiss',
                    'slips',
                ],
                'sense',
                [
                    Wordler::STATE_CORRECT,
                    Wordler::STATE_ABSENT,
                    Wordler::STATE_ABSENT,
                    Wordler::STATE_ABSENT,
                    Wordler::STATE_ABSENT,
                ],
                [
                    'skill',
                    'skimp',
                    'skirl',
                    'skirt',
                    'stair',
                    'swiss',
                    'slips',
                ]
            ],
            [
                [
                    'skill',
                    'skimp',
                    'skirl',
                    'skirt',
                    'stair',
                    'swiss',
                    'slips',
                ],
                'sisal',
                [
                    Wordler::STATE_CORRECT,
                    Wordler::STATE_PRESENT,
                    Wordler::STATE_ABSENT,
                    Wordler::STATE_ABSENT,
                    Wordler::STATE_CORRECT,
                ],
                [
                    'skill',
                    'skirl',
                ],
            ],
            [
                [
                    'glean',
                ],
                'sense',
                [
                    Wordler::STATE_ABSENT,
                    Wordler::STATE_PRESENT,
                    Wordler::STATE_PRESENT,
                    Wordler::STATE_ABSENT,
                    Wordler::STATE_ABSENT,
                ],
                [
                    'glean',
                ],
            ],
        ];
    }
}
