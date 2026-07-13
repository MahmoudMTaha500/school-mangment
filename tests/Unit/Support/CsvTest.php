<?php

namespace Tests\Unit\Support;

use App\Support\Csv;
use PHPUnit\Framework\TestCase;

final class CsvTest extends TestCase
{
    public function test_it_neutralises_formula_trigger_prefixes(): void
    {
        $this->assertSame("'=HYPERLINK(\"http://evil\")", Csv::field('=HYPERLINK("http://evil")'));
        $this->assertSame("'+1", Csv::field('+1'));
        $this->assertSame("'-2+3", Csv::field('-2+3'));
        $this->assertSame("'@SUM(A1)", Csv::field('@SUM(A1)'));
    }

    public function test_it_leaves_safe_values_and_numbers_untouched(): void
    {
        $this->assertSame('payment-intent', Csv::field('payment-intent'));
        $this->assertSame('', Csv::field(''));
        $this->assertSame(1500, Csv::field(1500));
        $this->assertNull(Csv::field(null));
    }

    public function test_it_sanitises_a_whole_row(): void
    {
        $this->assertSame([1, 'credit', "'=cmd"], Csv::row([1, 'credit', '=cmd']));
    }
}
