<?php

namespace Tests\Unit;

use App\Support\Money;
use PHPUnit\Framework\TestCase;

class MoneyTest extends TestCase
{
    public function test_it_formats_amounts_in_fcfa(): void
    {
        $this->assertSame('500 000 FCFA', Money::format(500000));
        $this->assertSame('0 FCFA', Money::format(0));
        $this->assertSame('-568 650 FCFA', Money::format(-568650));
    }
}
