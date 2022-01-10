<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    public function down()
    {
        Schema::dropIfExists('stocks');
        Schema::dropIfExists('items');
    }

    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->string('sku');
            $table->string('code');
            $table->string('name');
            $table->string('slug');
            $table->decimal('cost', 25, 4);
            $table->decimal('price', 25, 4);
            $table->string('rack')->nullable();
            $table->string('photo')->nullable();
            $table->text('details')->nullable();
            $table->string('summary')->nullable();
            $table->string('alt_name')->nullable();
            $table->string('symbology')->nullable();
            $table->string('type')->default('standard');
            $table->decimal('min_price', 25, 4)->nullable();
            $table->decimal('max_price', 25, 4)->nullable();
            $table->decimal('max_discount', 25, 4)->nullable();
            $table->boolean('expiry')->default('0')->nullable();
            $table->boolean('is_stock')->default('0')->nullable();
            $table->boolean('changeable')->default('0')->nullable();
            $table->boolean('tax_included')->default('0')->nullable();
            $table->decimal('alert_quantity', 15, 4)->nullable();
            $table->schemalessAttributes('extra_attributes');
            $table->string('hsn_number')->nullable();
            $table->string('dimensions')->nullable();
            $table->string('weight')->nullable();
            $table->uuid('supplier_id')->nullable();
            $table->string('supplier_item_id')->nullable();
            $table->boolean('hide_in_pos')->nullable();
            $table->boolean('hide_in_shop')->nullable();
            $table->boolean('has_serials')->nullable();
            $table->boolean('on_sale')->nullable();
            $table->timestamp('start_date')->nullable();
            $table->timestamp('end_date')->nullable();
            $table->boolean('has_variants')->default('0')->nullable();
            $table->text('variants')->nullable();
            $table->uuid('brand_id')->nullable();
            $table->uuid('unit_id')->nullable();
            $table->uuid('sale_unit_id')->nullable();
            $table->uuid('purchase_unit_id')->nullable();
            $table->timestamps();

            $table->index('sku');
            $table->unique('sku');
            $table->index('code');
            $table->unique('code');
            $table->unique('slug');
            $table->index('unit_id');
            $table->index('brand_id');
            $table->index('supplier_id');
            $table->unique('supplier_item_id');
            $table->foreign('unit_id')->references('id')->on('units');
            $table->foreign('brand_id')->references('id')->on('brands');
            $table->foreign('supplier_id')->references('id')->on('suppliers');
        });

        Schema::create('stocks', function (Blueprint $table) {
            $table->uuid('id');
            $table->primary('id');
            $table->uuid('item_id');
            $table->uuid('location_id');
            $table->string('rack')->nullable();
            $table->decimal('cost', 25, 4)->nullable();
            $table->decimal('price', 25, 4)->nullable();
            $table->decimal('avg_cost', 25, 4)->nullable();
            $table->decimal('quantity', 25, 4)->nullable();
            $table->decimal('alert_quantity', 15, 4)->nullable();

            $table->index('item_id');
            $table->index('location_id');
            $table->unique(['item_id', 'location_id']);
            $table->foreign('location_id')->references('id')->on('locations');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('cascade');
        });
    }
}
