<?php

namespace Modules\MPS\Tests\Feature;

use Barryvdh\DomPDF\Facade;
use Modules\MPS\Models\Item;
use Modules\MPS\Models\Unit;
use Illuminate\Http\Response;
use Modules\MPS\Models\Account;
use Modules\MPS\Models\Payment;
use Modules\MPS\Models\Category;
use Modules\MPS\Models\Customer;
use Modules\MPS\Models\Location;
use Modules\MPS\Models\Supplier;
use Modules\MPS\Tests\MPSTestCase;

class PdfTest extends MPSTestCase
{
    use TestHelpers;

    protected function setUp(): void
    {
        parent::setUp();
        $this->super    = $this->createUser('super');
        $this->unit     = factory(Unit::class)->create();
        $this->account  = factory(Account::class)->create();
        $this->category = factory(Category::class)->create();
        $this->customer = factory(Customer::class)->create(['user_id' => $this->super->id]);
        $this->supplier = factory(Supplier::class)->create(['user_id' => $this->super->id]);
        $this->location = factory(Location::class)->create(['account_id' => $this->account->id]);
        session(['location_id' => $this->location->id]);
        $this->item = factory(Item::class)->create();
        $this->item->categories()->sync($this->category->id);
        $this->item->stock()->create(['quantity' => 20]);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'name', 'mps_value' => 'Modern POS Solution']);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'short_name', 'mps_value' => 'MPS']);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'company', 'mps_value' => 'Tecdiary']);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'email', 'mps_value' => 'noreply@tecdiary.com']);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'phone', 'mps_value' => '011111442122']);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'address', 'mps_value' => 'Address']);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'default_logo', 'mps_value' => url('storage/images/logo.png')]);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'default_customer', 'mps_value' => $this->customer->id]);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'show_discount', 'mps_value' => 1]);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'show_tax', 'mps_value' => 1]);
        factory('Modules\MPS\Models\Setting')->create(['mps_key' => 'show_tax_summary', 'mps_value' => 1]);
    }

    public function testPurchasePDFDownload(): void
    {
        $settings       = json_decode(mps_config(), true);
        $this->purchase = $this->createPurchase($this, 1, $this->super);
        // $this->purchase->attributes = extra_attributes('purchase');
        $this->purchase->loadMissing(['location', 'supplier', 'payments', 'items' => fn ($q) => $q->withAll()]);
        $response = Facade::loadView('mps::pdf.purchase', ['settings' => $settings, 'purchase' => $this->purchase])->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testQuotationPDFDownload(): void
    {
        $settings  = json_decode(mps_config(), true);
        $quotation = $this->createQuotation($this, 1, $this->super);
        // $quotation->attributes = extra_attributes('quotation');
        $quotation->loadMissing(['location', 'customer', 'items' => fn ($q) => $q->withAll()]);
        $response = Facade::loadView('mps::pdf.quotation', ['settings' => $settings, 'quotation' => $quotation])->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testReturnOrderPDFDownload(): void
    {
        $settings     = json_decode(mps_config(), true);
        $return_order = $this->createReturnOrder($this, 1, 'sale', $this->super);
        // $return_order->attributes = extra_attributes('return_order');
        $return_order->loadMissing(['location', 'customer', 'supplier', 'payments', 'items' => fn ($q) => $q->withAll()]);
        $response = Facade::loadView('mps::pdf.return_order', ['settings' => $settings, 'return_order' => $return_order])->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testSalePaymentPDFDownload(): void
    {
        $settings = json_decode(mps_config(), true);
        $sale     = $this->createSale($this, 1, $this->super);
        // $sale->attributes = extra_attributes('sale');

        $payment = factory('Modules\MPS\Models\Payment')->make([
            'received'    => 1,
            'gateway'     => 'cash',
            'sale_id'     => $sale->id,
            'user_id'     => $sale->user_id,
            'account_id'  => $this->account->id,
            'customer_id' => $sale->customer_id,
            'amount'      => $sale->grand_total,
        ])->toArray();
        $this->actingAs($this->super)->ajax()->post(url(module('route')) . '/app/payments/', $payment)->assertOk();

        $payment = Payment::first();
        $payment->loadMissing(['account:id,name', 'location', 'payable']);
        $response = Facade::loadView('mps::pdf.payment', ['settings' => $settings, 'payment' => $payment])->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }

    public function testSalePDFDownload(): void
    {
        $settings = json_decode(mps_config(), true);
        $sale     = $this->createSale($this, 1, $this->super);
        // $sale->attributes = extra_attributes('sale');
        $sale->loadMissing(['location', 'customer', 'payments', 'items' => fn ($q) => $q->withAll()]);
        $response = Facade::loadView('mps::pdf.sale', ['settings' => $settings, 'sale' => $sale])->download('test.pdf');

        $this->assertInstanceOf(Response::class, $response);
        $this->assertNotEmpty($response->getContent());
        $this->assertEquals('application/pdf', $response->headers->get('Content-Type'));
        $this->assertEquals('attachment; filename="test.pdf"', $response->headers->get('Content-Disposition'));
    }
}
