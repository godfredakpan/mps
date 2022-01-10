<?php

namespace Modules\MPS\Tests\Unit;

use Modules\MPS\Models\Tax;
use Modules\MPS\Actions\TaxAction;
use Modules\MPS\Tests\MPSTestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaxCalculationTest extends MPSTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        activity()->disableLogging();
        $this->super = $this->createUser('super');
    }

    public function testExcludedTax()
    {
        $tax1 = factory(Tax::class)->create(['rate' => 10, 'compound' => false]);

        $result = (new TaxAction)->calculate([$tax1->id], 100, 1, false);
        $this->assertEquals(10, $result->first()['total_amount']);

        $result = (new TaxAction)->calculate([$tax1->id], 200, 1, false);
        $this->assertEquals(20, $result->first()['total_amount']);

        $result = (new TaxAction)->calculate([$tax1->id], 100, 3, false);
        $this->assertEquals(30, $result->first()['total_amount']);

        $tax2   = factory(Tax::class)->create(['rate' => 10, 'compound' => true]);
        $result = (new TaxAction)->calculate([$tax1->id, $tax2->id], 100, 1, false);
        foreach ($result as $key => $value) {
            if ($key == $tax1->id) {
                $this->assertEquals(10, $value['total_amount']);
            } elseif ($key == $tax2->id) {
                $this->assertEquals(11, $value['total_amount']);
                $this->assertEquals(110, $value['calculated_on']);
            }
        }

        $tax3   = factory(Tax::class)->create(['rate' => 10, 'compound' => true]);
        $result = (new TaxAction)->calculate([$tax1->id, $tax2->id, $tax3->id], 100, 2, false);
        foreach ($result as $key => $value) {
            if ($key == $tax1->id) {
                $this->assertEquals(20, $value['total_amount']);
            } elseif ($key == $tax2->id) {
                $this->assertEquals(22, $value['total_amount']);
                $this->assertEquals(110, $value['calculated_on']);
            } elseif ($key == $tax3->id) {
                $this->assertEquals(22, $value['total_amount']);
                $this->assertEquals(110, $value['calculated_on']);
            }
        }
    }

    public function testIncludedTax()
    {
        $tax1 = factory(Tax::class)->create(['rate' => 10, 'compound' => false]);

        $result = (new TaxAction)->calculate([$tax1->id], 110, 1, true);
        $this->assertEquals(10, $result->first()['total_amount']);

        $result = (new TaxAction)->calculate([$tax1->id], 220, 1, true);
        $this->assertEquals(20, $result->first()['total_amount']);

        $result = (new TaxAction)->calculate([$tax1->id], 110, 3, true);
        $this->assertEquals(30, $result->first()['total_amount']);

        $tax2   = factory(Tax::class)->create(['rate' => 10, 'compound' => true]);
        $result = (new TaxAction)->calculate([$tax1->id, $tax2->id], 110, 1, true);
        foreach ($result as $key => $value) {
            if ($key == $tax1->id) {
                // should be calculated on 110 - 10
                $this->assertEquals(9.0909, $value['total_amount']);
            } elseif ($key == $tax2->id) {
                $this->assertEquals(10, $value['total_amount']);
                $this->assertEquals(110, $value['calculated_on']);
            }
        }

        $tax3   = factory(Tax::class)->create(['rate' => 10, 'compound' => true]);
        $result = (new TaxAction)->calculate([$tax1->id, $tax2->id, $tax3->id], 110, 2, true);
        foreach ($result as $key => $value) {
            if ($key == $tax1->id) {
                // should be calculated on 110 - 10 - 10
                $this->assertEquals(8.1818 * 2, $value['total_amount']);
            } elseif ($key == $tax2->id) {
                $this->assertEquals(20, $value['total_amount']);
                $this->assertEquals(110, $value['calculated_on']);
            } elseif ($key == $tax3->id) {
                $this->assertEquals(20, $value['total_amount']);
                $this->assertEquals(110, $value['calculated_on']);
            }
        }
    }
}
