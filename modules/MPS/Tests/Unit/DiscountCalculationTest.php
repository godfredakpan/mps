<?php

namespace Modules\MPS\Tests\Unit;

use Modules\MPS\Tests\MPSTestCase;
use Modules\MPS\Actions\DiscountAction;

class DiscountCalculationTest extends MPSTestCase
{
    public function testDiscount()
    {
        $discount = (new DiscountAction)->calculate(10, 100);
        $this->assertEquals(10, $discount);

        $discount = (new DiscountAction)->calculate(10, 200);
        $this->assertEquals(20, $discount);
    }
}
