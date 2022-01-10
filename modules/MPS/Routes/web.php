<?php

use Nwidart\Modules\Facades\Module;
use Illuminate\Support\Facades\Route;

$route  = module_data('MPS', 'route');
$module = Module::find('Shop');
if (!$module || !$module->isEnabled()) {
    Route::redirect('/', '/' . $route);
}
// Route::redirect('/login', '/' . $route . '/#/login')->name('login');
Route::prefix($route)->middleware('staff')->group(function () {
    // MPS Home
    Route::view('/', 'mps::home')->name('mps');

    // Public blade view
    Route::prefix('views')->group(function () {
        // Route::view('/', 'mps::views')->name('views');
        Route::get('sale/{hash}', 'OrderViewController@sale');
        Route::get('payment/{hash}', 'OrderViewController@payment');
        Route::get('purchase/{hash}', 'OrderViewController@purchase');
        Route::get('quotation/{hash}', 'OrderViewController@quotation');
        Route::get('return_order/{hash}', 'OrderViewController@returnOrder');
    });
    // Route::post('pay/{gateway}/{hash}', 'PublicController@pay')->name('pay');
    Route::get('view/{act}/{hash}', 'OrderViewController@index')->name('order')->middleware('signed');

    // PayPal Standard
    Route::post('paypal/ipn', 'PublicController@ipn')->name('paypal.ipn');
    Route::get('paypal/{hash}', 'PublicController@paypal')->name('paypal.payment');
    Route::get('paypal/{hash}/completed', 'PublicController@completed')->name('paypal.completed');

    // PDF download routes
    Route::prefix('download')->middleware('auth')->group(function () {
        Route::get('sales/{sale}', 'SaleController@download')->name('sales.download');
        Route::get('payments/{payment}', 'PaymentController@download')->name('payments.download');
        Route::get('purchases/{purchase}', 'PurchaseController@download')->name('purchases.download');
        Route::get('quotations/{quotation}', 'QuotationController@download')->name('quotations.download');
        Route::get('return_orders/{return_order}', 'ReturnOrderController@download')->name('return-orders.download');
    });

    // MPS App
    Route::prefix('app')->group(function () {
        Route::get('token', 'AjaxController@token');
        Route::get('language/{locale}', 'AjaxController@locale');
        Route::post('pay/{gateway}/{hash}', 'PublicController@pay')->name('pay');
        // Route::get('auth/{user:username}/login', 'AjaxController@login')->name('auth.user');

        Route::middleware('auth')->group(function () {
            // Impersonate routes
            // Route::middleware('impersonate')->group(function () {});
            Route::post('impersonate/{user:username}/url', 'AjaxController@impersonateUrl');
            Route::post('impersonate/stop', 'UserController@stopImpersonate')->name('impersonate.stop');
            Route::post('impersonate/{user:username}', 'UserController@impersonate')->name('impersonate.start');

            Route::get('me', 'UserController@me');
            Route::get('pos', 'PosController@index');
            Route::get('alerts', 'AlertController@index');
            Route::get('states', 'AjaxController@states');
            Route::post('location', 'AjaxController@location');
            Route::get('countries', 'AjaxController@countries');
            Route::get('dashboard', 'DashboardController@chart');
            Route::get('alerts/expiring', 'AlertController@expiring');
            Route::get('alerts/quantity', 'AlertController@quantity');
            Route::get('dashboard/year', 'DashboardController@yearChart');
            Route::get('dashboard/month', 'DashboardController@monthChart');

            // POS register
            Route::get('pos/register/{register_record}', 'RegisterRecordController@show');
            Route::post('pos/register/{register_record}/close', 'RegisterRecordController@close');

            // Search routes // TODO: check permissions and controller methods for Search
            Route::get('sales/search', 'SaleController@search')->name('sales.search');
            Route::get('halls/search', 'SearchController@halls')->name('halls.search');
            Route::get('taxes/search', 'SearchController@taxes')->name('taxes.search');
            Route::get('roles/search', 'SearchController@roles')->name('roles.search');
            Route::get('units/search', 'SearchController@units')->name('units.search');
            Route::get('users/search', 'SearchController@users')->name('users.search');
            Route::get('orders/search', 'OrderController@search')->name('orders.search');
            Route::get('brands/search', 'SearchController@brands')->name('brands.search');
            Route::get('tables/search', 'SearchController@tables')->name('tables.search');
            Route::get('quotations/search', 'SaleController@search')->name('quotations.search');
            Route::get('payments/search', 'PaymentController@search')->name('payments.search');
            Route::get('accounts/search', 'SearchController@accounts')->name('accounts.search');
            Route::get('purchases/search', 'PurchaseController@search')->name('purchases.search');
            Route::get('customers/search', 'SearchController@customers')->name('customers.search');
            Route::get('modifiers/search', 'SearchController@modifiers')->name('modifiers.search');
            Route::get('locations/search', 'SearchController@locations')->name('locations.search');
            Route::get('suppliers/search', 'SearchController@suppliers')->name('suppliers.search');
            Route::get('categories/search', 'SearchController@categories')->name('categories.search');

            Route::get('gift_cards/search', 'GiftCardController@search')->name('gift-cards.search');
            Route::get('all_categories/search', 'SearchController@allCategories')->name('all-categories.search');
            Route::get('recurring_sales/search', 'RecurringSaleController@search')->name('recurring-sales.search');
            Route::get('customer_groups/search', 'SearchController@customerGroups')->name('customer-groups.search');

            // Routes where location is required
            Route::middleware('location')->group(function () {
                Route::get('items/search', 'SearchController@items');
                Route::get('items_wp/search', 'SearchController@itemsWP');
                Route::get('items_wt/search', 'SearchController@itemsWT');
                Route::get('sale_order_items/{item}', 'AddItemToOrderController@sale');
                Route::get('purchase_order_items/{item}', 'AddItemToOrderController@purchase');
                Route::get('items/trails/{item}', 'ItemTrailController@index')->name('items.trails');
                Route::get('items/trails/{item}/table', 'ItemTrailController@table')->name('items.trails.table');

                Route::post('sales', 'SaleController@store')->name('sales.store');
                Route::post('orders', 'OrderController@store')->name('orders.store');
                Route::post('incomes', 'IncomeController@store')->name('incomes.store');
                Route::post('payments', 'PaymentController@store')->name('payments.store');
                Route::post('expenses', 'ExpenseController@store')->name('expenses.store');
                Route::post('purchases', 'PurchaseController@store')->name('purchases.store');
                Route::post('quotations', 'QuotationController@store')->name('quotations.store');
                Route::post('register/{register}', 'AjaxController@register')->name('register.store');
                Route::post('return_orders', 'ReturnOrderController@store')->name('return-orders.store');
                Route::post('adjustments', 'StockAdjustmentController@store')->name('stock-adjustments.store');
                Route::post('recurring_sales', 'RecurringSaleController@store')->name('recurring-sales.store');
            });

            // Post routes
            Route::post('salaries/generate', 'SalaryController@generate')->name('salaries.generate');
            Route::post('sales/{sale}/deliveries', 'DeliveryController@store')->name('deliveries.store');

            // List Routes
            Route::get('media/{media}', 'MediaController@show');
            Route::get('gift_cards/logs/{gift_card}', 'GiftCardController@logs');
            Route::post('expenses/{expense}/approve', 'ExpenseController@approve');

            // List order payments
            Route::get('sales/{sale}/payments', 'SaleController@payments');
            Route::get('purchases/{purchase}/payments', 'PurchaseController@payments');

            // Customer addresses & users
            Route::post('pos/add_customer', 'PosController@addCustomer')->name('pos.customers.add');
            Route::post('customers/add_user', 'CustomerController@addUser')->name('customers.users.add');
            Route::get('customers/users/{customer_id}', 'CustomerController@users')->name('customers.users');
            Route::post('customers/add_address', 'CustomerController@addAddress')->name('customers.addresses.add');
            Route::put('customers/update_user/{user}', 'CustomerController@updateUser')->name('customers.users.update');
            Route::get('customers/addresses/{customer_id}', 'CustomerController@addresses')->name('customers.addresses');
            Route::put('customers/update_address/{address}', 'CustomerController@updateAddress')->name('customers.addresses.update');

            // Transaction routes
            Route::get('customers/transactions/{customer}', 'TransactionController@customer')->name('customers.transactions');
            Route::get('suppliers/transactions/{supplier}', 'TransactionController@supplier')->name('suppliers.transactions');
            Route::get('customers/transactions/{customer}/table', 'TransactionController@customerTable')->name('customers.transactions.table');
            Route::get('suppliers/transactions/{supplier}/table', 'TransactionController@supplierTable')->name('suppliers.transactions.table');

            // Resource routes
            Route::resource('taxes', 'TaxController')->except(['edit']);
            Route::resource('halls', 'HallController')->except(['edit']);
            Route::resource('items', 'ItemController')->except(['edit']);
            Route::resource('units', 'UnitController')->except(['edit']);
            Route::resource('brands', 'BrandController')->except(['edit']);
            Route::resource('events', 'EventController')->except(['edit']);
            Route::resource('fields', 'FieldController')->except(['edit']);
            Route::resource('tables', 'HallTableController')->except(['edit']);
            Route::resource('promos', 'PromotionController')->except(['edit']);
            Route::resource('modifiers', 'ModifierController')->except(['edit']);
            Route::resource('categories', 'CategoryController')->except(['edit']);

            Route::resource('payments', 'PaymentController')->except(['edit', 'store']);
            Route::resource('salaries', 'SalaryController')->except(['create', 'edit']);
            Route::resource('customers', 'CustomerController')->except(['edit', 'update']);
            Route::resource('suppliers', 'SupplierController')->except(['edit', 'update']);
            Route::resource('sales', 'SaleController')->except(['edit', 'store', 'update']);
            Route::resource('orders', 'OrderController')->except(['edit', 'store', 'update']);
            Route::resource('incomes', 'IncomeController')->except(['edit', 'store', 'update']);
            Route::resource('expenses', 'ExpenseController')->except(['edit', 'store', 'update']);
            Route::resource('purchases', 'PurchaseController')->except(['edit', 'store', 'update']);
            Route::resource('deliveries', 'DeliveryController')->except(['edit', 'store', 'update']);
            Route::resource('quotations', 'QuotationController')->except(['edit', 'store', 'update']);

            Route::resource('gift_cards', 'GiftCardController', ['names' => 'gift-cards'])->except(['edit']);
            Route::resource('customer_groups', 'CustomerGroupController', ['names' => 'customer-groups'])->except(['edit']);
            Route::resource('transfers/asset', 'AssetTransferController', ['names' => 'asset-transfers'])->except(['edit', 'update']);
            Route::resource('transfers/stock', 'StockTransferController', ['names' => 'stock-transfers'])->except(['edit', 'update']);
            Route::resource('return_orders', 'ReturnOrderController', ['names' => 'return-orders'])->except(['edit', 'store', 'update']);
            Route::resource('adjustments', 'StockAdjustmentController', ['names' => 'stock-adjustments'])->except(['edit', 'store', 'update']);
            Route::resource('recurring_sales', 'RecurringSaleController', ['names' => 'recurring-sales'])->except(['edit', 'store', 'update']);

            // Update routes
            Route::put('deliveries/{delivery}/', 'DeliveryController@update')->name('deliveries.update');
            Route::put('sales/{sale}', 'SaleController@update')->middleware('can:update,sale')->name('sales.update');
            Route::put('orders/{order}', 'OrderController@update')->middleware('can:update,order')->name('orders.update');
            Route::put('incomes/{income}', 'IncomeController@update')->middleware('can:update,income')->name('incomes.update');
            Route::put('expenses/{expense}', 'ExpenseController@update')->middleware('can:update,expense')->name('expenses.update');
            Route::put('customers/{customer}', 'CustomerController@update')->middleware('can:update,customer')->name('customers.update');
            Route::put('purchases/{purchase}', 'PurchaseController@update')->middleware('can:update,purchase')->name('purchases.update');
            Route::put('suppliers/{supplier}', 'SupplierController@update')->middleware('can:update,supplier')->name('suppliers.update');
            Route::put('quotations/{quotation}', 'QuotationController@update')->middleware('can:update,quotation')->name('quotations.update');

            Route::put('transfers/asset/{asset}', 'AssetTransferController@update')->middleware('can:update,asset')->name('asset-transfers.update');
            Route::put('transfers/stock/{stock}', 'StockTransferController@update')->middleware('can:update,stock')->name('stock-transfers.update');
            Route::put('return_orders/{return_order}', 'ReturnOrderController@update')->middleware('can:update,return_order')->name('return-orders.update');
            Route::put('adjustments/{adjustment}/', 'StockAdjustmentController@update')->middleware('can:update,adjustment')->name('stock-adjustments.update');
            Route::put('recurring_sales/{recurring_sale}', 'RecurringSaleController@update')->middleware('can:update,recurring_sale')->name('recurring-sales.update');

            // Email routes
            Route::post('sales/email/{sale}', 'SaleController@email')->name('sales.email');
            Route::post('payments/email/{payment}', 'PaymentController@email')->name('payments.email');
            Route::post('purchases/email/{purchase}', 'PurchaseController@email')->name('purchases.email');
            Route::post('quotations/email/{quotation}', 'QuotationController@email')->name('quotations.email');
            Route::post('return_orders/email/{return_order}', 'ReturnOrderController@email')->name('return-orders.email');

            // Review payment route
            Route::post('payments/review/{payment}', 'PaymentController@review')->name('payments.review');

            // User routes
            Route::get('profile', 'ProfileController@index')->name('profile.read');
            Route::post('profile', 'ProfileController@update')->name('profile.update');
            Route::post('profile/change_password', 'ProfileController@changePassword')->name('profile.change-password');

            // Super user routes
            Route::middleware('role:super')->group(function () {
                Route::resource('roles', 'RoleController')->except(['edit']);
                Route::resource('users', 'UserController')->except(['edit']);
                Route::resource('accounts', 'AccountController')->except(['edit']);
                Route::resource('locations', 'LocationController')->except(['edit']);

                // List routes
                Route::get('accounts/transactions/{account}', 'TransactionController@account')->name('accounts.transactions');
                Route::get('accounts/transactions/{account}/table', 'TransactionController@accountTable')->name('accounts.transactions.table');

                // Settings
                Route::get('settings', 'SettingController@index');
                Route::post('settings', 'SettingController@store');
                Route::put('settings', 'SettingController@update');
                Route::post('settings/logo', 'SettingController@logo');
                Route::get('settings/label', 'SettingController@label');
                Route::post('settings/label', 'SettingController@saveLabel');
                Route::post('settings/barcode', 'SettingController@saveBarcode');
                Route::post('roles/{role}/permissions', 'RoleController@setPermissions');
            });

            // Delete
            Route::delete('sales/{sale}/toggle_void', 'SaleController@toggleVoid');
            Route::delete('attachments/{attachment}', 'MediaController@deleteAttachment');
            Route::delete('purchases/{purchase}/toggle_void', 'PurchaseController@toggleVoid');
            // Route::delete('media/{media}', 'MediaController@destroy')->name('media.destroy'); // TODO Media delete permission

            // Route::delete('taxes/{tax}', 'TaxController@destroy')->name('taxes.destroy');
            // Route::delete('items/{item}', 'ItemController@destroy')->name('items.destroy');
            // Route::delete('halls/{hall}', 'HallController@destroy')->name('halls.destroy');
            // Route::delete('units/{unit}', 'UnitController@destroy')->name('units.destroy');
            // Route::delete('sales/{sale}', 'SaleController@destroy')->name('sales.destroy');
            // Route::delete('brands/{brand}', 'BrandController@destroy')->name('brands.destroy');
            // Route::delete('fields/{field}', 'FieldController@destroy')->name('fields.destroy');
            // Route::delete('orders/{order}', 'OrderController@destroy')->name('orders.destroy');
            // Route::delete('incomes/{income}', 'IncomeController@destroy')->name('incomes.destroy');
            // Route::delete('promos/{promo}', 'PromotionController@destroy')->name('promos.destroy');
            // Route::delete('tables/{table}', 'HallTableController@destroy')->name('tables.destroy');
            // Route::delete('expenses/{expense}', 'ExpenseController@destroy')->name('expenses.destroy');
            // Route::delete('payments/{payment}', 'PaymentController@destroy')->name('payments.destroy');
            // Route::delete('customers/{customer}', 'CustomerController@destroy')->name('customers.destroy');
            // Route::delete('locations/{location}', 'LocationController@destroy')->name('locations.destroy');
            // Route::delete('modifiers/{modifier}', 'ModifierController@destroy')->name('modifiers.destroy');
            // Route::delete('purchases/{purchase}', 'PurchaseController@destroy')->name('purchases.destroy');
            // Route::delete('suppliers/{supplier}', 'SupplierController@destroy')->name('suppliers.destroy');
            // Route::delete('categories/{category}', 'CategoryController@destroy')->name('categories.destroy');
            // Route::delete('gift_cards/{gift_card}', 'GiftCardController@destroy')->name('gift-cards.destroy');
            // Route::delete('quotations/{quotation}', 'QuotationController@destroy')->name('quotations.destroy');
            // Route::delete('transfers/asset/{asset}', 'AssetTransferController@destroy')->name('asset-transfers.destroy');
            // Route::delete('transfers/stock/{stock}', 'StockTransferController@destroy')->name('stock-transfers.destroy');
            // Route::delete('return_orders/{return_order}', 'ReturnOrderController@destroy')->name('return-orders.destroy');
            // Route::delete('customer_groups/{customer_group}', 'CustomerGroupController@destroy')->name('customer-groups.destroy');

            // Delete many
            Route::post('taxes/delete/many', 'TaxController@destroyMany')->name('taxes.bulk-delete');
            Route::post('items/delete/many', 'ItemController@destroyMany')->name('items.bulk-delete');
            Route::post('halls/delete/many', 'HallController@destroyMany')->name('halls.bulk-delete');
            Route::post('units/delete/many', 'UnitController@destroyMany')->name('units.bulk-delete');
            Route::post('sales/delete/many', 'SaleController@destroyMany')->name('sales.bulk-delete');
            Route::post('media/delete/many', 'MediaController@destroyMany')->name('media.bulk-delete');
            Route::post('brands/delete/many', 'BrandController@destroyMany')->name('brands.bulk-delete');
            Route::post('fields/delete/many', 'FieldController@destroyMany')->name('fields.bulk-delete');
            Route::post('orders/delete/many', 'OrderController@destroyMany')->name('orders.bulk-delete');
            Route::post('incomes/delete/many', 'IncomeController@destroyMany')->name('incomes.bulk-delete');
            Route::post('promos/delete/many', 'PromotionController@destroyMany')->name('promos.bulk-delete');
            Route::post('tables/delete/many', 'HallTableController@destroyMany')->name('tables.bulk-delete');
            Route::post('expenses/delete/many', 'ExpenseController@destroyMany')->name('expenses.bulk-delete');
            Route::post('payments/delete/many', 'PaymentController@destroyMany')->name('payments.bulk-delete');
            Route::post('customers/delete/many', 'CustomerController@destroyMany')->name('customers.bulk-delete');
            Route::post('locations/delete/many', 'LocationController@destroyMany')->name('locations.bulk-delete');
            Route::post('modifiers/delete/many', 'ModifierController@destroyMany')->name('modifiers.bulk-delete');
            Route::post('purchases/delete/many', 'PurchaseController@destroyMany')->name('purchases.bulk-delete');
            Route::post('suppliers/delete/many', 'SupplierController@destroyMany')->name('suppliers.bulk-delete');
            Route::post('categories/delete/many', 'CategoryController@destroyMany')->name('categories.bulk-delete');
            Route::post('gift_cards/delete/many', 'GiftCardController@destroyMany')->name('gift-cards.bulk-delete');
            Route::post('quotations/delete/many', 'QuotationController@destroyMany')->name('quotations.bulk-delete');
            Route::post('return_orders/delete/many', 'ReturnOrderController@destroyMany')->name('return-orders.bulk-delete');
            Route::post('transfers/asset/delete/many', 'AssetTransferController@destroyMany')->name('asset-transfers.bulk-delete');
            Route::post('transfers/stock/delete/many', 'StockTransferController@destroyMany')->name('stock-transfers.bulk-delete');
            Route::post('customer_groups/delete/many', 'CustomerGroupController@destroyMany')->name('customer-groups.bulk-delete');

            Route::get('logs', 'UtilityController@logs')->name('activity_logs.report');
            Route::get('reports/time_clocks', 'TimeClockController@index')->name('time_clock.report');
            Route::get('reports/registers', 'RegisterRecordController@index')->name('registers.report');
            Route::get('logs/{activity}', 'UtilityController@showLog')->name('activity_logs.report.show');
            Route::prefix('reports')->namespace('Reports')->group(function () {
                Route::get('totals', 'ReportController@index')->name('read.report');
                Route::get('taxes', 'TaxReportController@index')->name('taxes.report');
                Route::get('items', 'ItemReportController@index')->name('items.report');
                Route::get('sales', 'SaleReportController@index')->name('sales.report');
                Route::get('items/top', 'ItemReportController@top')->name('items.report.top');
                Route::get('incomes', 'IncomeReportController@index')->name('incomes.report');
                Route::get('expenses', 'ExpenseReportController@index')->name('expenses.report');
                Route::get('payments', 'PaymentReportController@index')->name('payments.report');
                Route::get('sales/table', 'SaleReportController@table')->name('sales.report.table');
                Route::get('purchases', 'PurchaseReportController@index')->name('purchases.report');
                Route::get('categories', 'CategoryReportController@index')->name('categories.report');
                Route::get('incomes/table', 'IncomeReportController@table')->name('incomes.report.table');
                Route::get('expenses/table', 'ExpenseReportController@table')->name('expenses.report.table');
                Route::get('payments/table', 'PaymentReportController@table')->name('payments.report.table');
                Route::get('purchases/table', 'PurchaseReportController@table')->name('purchases.report.table');
                Route::get('adjustments', 'StockAdjustmentReportController@index')->name('stock_adjustments.report');
                Route::get('stock_transfers', 'StockTransferReportController@index')->name('stock_transfers.report');
                Route::get('adjustments/table', 'StockAdjustmentReportController@table')->name('stock_adjustments.report.table');
                Route::get('stock_transfers/table', 'StockTransferReportController@table')->name('stock_transfers.report.table');
            });

            // App Notification Routes
            Route::prefix('notifications')->group(function () {
                Route::get('/', 'NotificationController@index');
                Route::put('/{id}', 'NotificationController@update');
                Route::post('/{id}', 'NotificationController@unread');
                Route::delete('/{id}', 'NotificationController@destroy');
                Route::post('/read/all', 'NotificationController@updateAll');
                Route::post('/delete/all', 'NotificationController@destroyAll');
            });
        });
    });

    // Notification preview
    Route::prefix('notifications')->group(function () {
        Route::prefix('payment')->group(function () {
            Route::get('created', function () {
                $payment = \Modules\MPS\Models\Payment::latest()->first();
                $message = (new \Modules\MPS\Notifications\PaymentCreated($payment, false))->toMail($payment);
                $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));
                return $markdown->render('vendor.notifications.email', $message->data());
            });
            Route::get('received', function () {
                $payment = \Modules\MPS\Models\Payment::latest()->first();
                $message = (new \Modules\MPS\Notifications\PaymentReceived($payment, false))->toMail($payment);
                $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));
                return $markdown->render('vendor.notifications.email', $message->data());
            });
            Route::get('reminder', function () {
                $payment = \Modules\MPS\Models\Payment::latest()->first();
                $message = (new \Modules\MPS\Notifications\PaymentReminder($payment, false))->toMail($payment);
                $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));
                return $markdown->render('vendor.notifications.email', $message->data());
            });
        });
        Route::get('purchase', function () {
            $purchase = \Modules\MPS\Models\Purchase::latest()->first();
            $message = (new \Modules\MPS\Notifications\PurchaseCreated($purchase, false))->toMail($purchase);
            $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));
            return $markdown->render('vendor.notifications.email', $message->data());
        });
        Route::get('sale', function () {
            $sale = \Modules\MPS\Models\Sale::latest()->first();
            $message = (new \Modules\MPS\Notifications\SaleCreated($sale, false))->toMail($sale);
            $markdown = new \Illuminate\Mail\Markdown(view(), config('mail.markdown'));
            return $markdown->render('vendor.notifications.email', $message->data());
        });
    });

    // PDF routes
    Route::get('/pdf/sale/{sale}', 'Pdfs\SaleController')->name('pdf.sale');
    Route::get('/pdf/payment/{payment}', 'Pdfs\PaymentController')->name('pdf.payment');
    Route::get('/pdf/purchase/{purchase}', 'Pdfs\PurchaseController')->name('pdf.purchase');
    Route::get('/pdf/quotation/{quotation}', 'Pdfs\QuotationController')->name('pdf.quotation');
    Route::get('/pdf/return_order/{return_order}', 'Pdfs\ReturnOrderController')->name('pdf.return_order');

    // Show home for all others
    Route::view('{any}', 'mps::home')->where('any', '.*');
});
