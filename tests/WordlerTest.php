<?php

declare(strict_types=1);

namespace Ttskch\Wordler;

use PHPUnit\Framework\TestCase;

class WordlerTest extends TestCase
{
    /** @var Wordler */
    protected $wordler;

    protected function setUp(): void
    {
        $this->wordler = new Wordler();
    }

    public function testIsInstanceOfWordler(): void
    {
        $actual = $this->wordler;
        $this->assertInstanceOf(Wordler::class, $actual);
    }
}
