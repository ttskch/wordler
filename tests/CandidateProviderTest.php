<?php

declare(strict_types=1);

namespace Ttskch\Wordler;

use PHPUnit\Framework\TestCase;

class CandidateProviderTest extends TestCase
{
    public function testAddHistory(): void
    {
        $SUT = new CandidateProvider([
            'skill',
            'skimp',
            'skint',
            'skirl',
            'skirt',
            'skive',
            'stair',
            'swiss',
            'slips',
        ]);

        $SUT->addHistory('sense', [
            Wordler::STATE_CORRECT,
            Wordler::STATE_ABSENT,
            Wordler::STATE_ABSENT,
            Wordler::STATE_ABSENT,
            Wordler::STATE_ABSENT,
        ]);

        $this->assertEquals([
            'skill',
            'skimp',
            'skirl',
            'skirt',
            'stair',
            'swiss',
            'slips',
        ], $SUT->getCandidates());

        $SUT = new CandidateProvider([
            'skill',
            'skimp',
            'skirl',
            'skirt',
            'stair',
            'swiss',
            'slips',
        ]);

        $SUT->addHistory('sisal', [
            Wordler::STATE_CORRECT,
            Wordler::STATE_PRESENT,
            Wordler::STATE_ABSENT,
            Wordler::STATE_ABSENT,
            Wordler::STATE_CORRECT,
        ]);

        $this->assertEquals([
            'skill',
            'skirl',
        ], $SUT->getCandidates());
    }
}
