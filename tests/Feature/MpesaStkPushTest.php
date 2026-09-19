<?php

namespace Tests\Feature;

use App\Models\MpesaTransaction;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class MpesaStkPushTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'mpesa.environment' => 'sandbox',
            'mpesa.consumer_key' => 'test-key',
            'mpesa.consumer_secret' => 'test-secret',
            'mpesa.shortcode' => '174379',
            'mpesa.passkey' => 'test-passkey',
            'mpesa.callback_url' => 'https://example.test/api/mpesa/callback',
            'mpesa.oauth_url' => 'https://sandbox.safaricom.co.ke/oauth/v1/generate?grant_type=client_credentials',
            'mpesa.stk_push_url' => 'https://sandbox.safaricom.co.ke/mpesa/stkpush/v1/processrequest',
            'mpesa.transaction_type' => 'CustomerPayBillOnline',
            'mpesa.token_cache_key' => 'mpesa.test.token',
        ]);
    }

    private function cashier(): User
    {
        $role = Role::query()->create([
            'name' => 'Cashier',
            'slug' => 'cashier-'.uniqid(),
            'description' => 'Test',
        ]);
        $perm = Permission::query()->firstOrCreate(
            ['name' => 'create_sale'],
            ['slug' => 'create_sale', 'description' => 'create_sale']
        );
        $role->permissions()->sync([$perm->id]);

        $user = User::factory()->create([
            'is_admin' => false,
            'role_id' => $role->id,
        ]);
        if (Schema::hasTable('role_user')) {
            $user->roles()->sync([$role->id]);
        }

        return $user;
    }

    private function seedCart(User $user, float $price = 100): void
    {
        $product = Product::query()->create([
            'name' => 'Test Tee',
            'slug' => 'test-tee-'.uniqid(),
            'sku' => 'SKU-'.uniqid(),
            'price' => $price,
            'stock' => 50,
            'is_active' => true,
        ]);

        $this->actingAs($user)->withSession([
            'pos_cart' => [
                'p'.$product->id => [
                    'product_id' => $product->id,
                    'product_variant_id' => null,
                    'name' => $product->name,
                    'sku' => $product->sku,
                    'price' => $price,
                    'qty' => 1,
                ],
            ],
        ]);
    }

    private function fakeDarajaSuccess(): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/*' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => '3599',
            ], 200),
            'sandbox.safaricom.co.ke/mpesa/stkpush/*' => Http::response([
                'MerchantRequestID' => 'mr-1',
                'CheckoutRequestID' => 'ws_CO_123',
                'ResponseCode' => '0',
                'ResponseDescription' => 'Success. Request accepted for processing',
                'CustomerMessage' => 'Success. Request accepted for processing',
            ], 200),
        ]);
    }

    public function test_stk_push_rejects_invalid_phone(): void
    {
        $user = $this->cashier();
        $this->seedCart($user);

        $response = $this->actingAs($user)->postJson(route('admin.mpesa.stk-push'), [
            'phone' => '123',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseCount('mpesa_transactions', 0);
    }

    public function test_stk_push_rejects_empty_cart_amount(): void
    {
        $user = $this->cashier();

        $response = $this->actingAs($user)->postJson(route('admin.mpesa.stk-push'), [
            'phone' => '0712345678',
        ]);

        $response->assertStatus(422);
        $this->assertStringContainsString('product', strtolower($response->json('message') ?? ''));
    }

    public function test_successful_daraja_initiation_creates_pending_transaction(): void
    {
        $this->fakeDarajaSuccess();
        $user = $this->cashier();
        $this->seedCart($user, 150);

        $response = $this->actingAs($user)->postJson(route('admin.mpesa.stk-push'), [
            'phone' => '0712345678',
        ]);

        $response->assertOk()
            ->assertJson([
                'success' => true,
                'checkout_request_id' => 'ws_CO_123',
                'status' => 'pending',
            ]);

        $this->assertDatabaseHas('mpesa_transactions', [
            'checkout_request_id' => 'ws_CO_123',
            'status' => 'pending',
            'phone_number' => '254712345678',
            'sale_id' => null,
        ]);
        $this->assertSame(0, Order::query()->count());
    }

    public function test_failed_daraja_initiation_marks_transaction_failed(): void
    {
        Http::fake([
            'sandbox.safaricom.co.ke/oauth/*' => Http::response([
                'access_token' => 'test-access-token',
                'expires_in' => '3599',
            ], 200),
            'sandbox.safaricom.co.ke/mpesa/stkpush/*' => Http::response([
                'requestId' => 'x',
                'errorCode' => '400.002.02',
                'errorMessage' => 'Bad Request - Invalid Amount',
            ], 400),
        ]);

        $user = $this->cashier();
        $this->seedCart($user);

        $response = $this->actingAs($user)->postJson(route('admin.mpesa.stk-push'), [
            'phone' => '0712345678',
        ]);

        $response->assertStatus(422)->assertJson(['success' => false]);
        $this->assertDatabaseHas('mpesa_transactions', [
            'status' => 'failed',
        ]);
        $this->assertSame(0, Order::query()->count());
    }

    public function test_successful_callback_completes_sale_once(): void
    {
        $user = $this->cashier();

        // Multi-location inventory is enabled when tables exist; seed stock there.
        $settings = \App\Models\Setting::get_settings();
        if ($settings->exists) {
            $settings->forceFill(['allow_negative_stock' => true, 'require_open_shift' => false])->save();
        } else {
            \App\Models\Setting::query()->create([
                'site_name' => 'Test',
                'allow_negative_stock' => true,
                'require_open_shift' => false,
            ]);
        }

        $product = Product::query()->create([
            'name' => 'Hoodie',
            'slug' => 'hoodie-'.uniqid(),
            'sku' => 'H-'.uniqid(),
            'price' => 200,
            'stock' => 10,
            'is_active' => true,
        ]);

        if (Schema::hasTable('stock_locations') && Schema::hasTable('inventory_stocks')) {
            $location = \App\Models\StockLocation::query()->firstOrCreate(
                ['code' => 'SHOP'],
                ['name' => 'Shop Floor', 'is_active' => true, 'sort_order' => 1]
            );
            \App\Models\InventoryStock::query()->updateOrCreate(
                [
                    'product_id' => $product->id,
                    'product_variant_id' => 0,
                    'stock_location_id' => $location->id,
                ],
                ['quantity' => 10]
            );
            $settings = \App\Models\Setting::get_settings();
            if ($settings->exists) {
                $settings->forceFill(['pos_location_id' => $location->id, 'allow_negative_stock' => false])->save();
            }
        }

        $txn = MpesaTransaction::create([
            'phone_number' => '254712345678',
            'amount' => 200,
            'merchant_request_id' => 'mr-1',
            'checkout_request_id' => 'ws_CO_SUCCESS',
            'status' => MpesaTransaction::STATUS_PENDING,
            'account_reference' => 'POSTEST001',
            'initiated_by' => $user->id,
            'checkout_payload' => [
                'cart' => [
                    'p'.$product->id => [
                        'product_id' => $product->id,
                        'product_variant_id' => null,
                        'name' => $product->name,
                        'sku' => $product->sku,
                        'price' => 200,
                        'qty' => 1,
                    ],
                ],
                'totals' => [
                    'subtotal' => 200,
                    'tax' => 0,
                    'discount' => 0,
                    'coupon_discount' => 0,
                    'sale_discount' => 0,
                    'loyalty_discount' => 0,
                    'loyalty_points_redeemed' => 0,
                    'coupon_code' => null,
                    'total' => 200,
                    'count' => 1,
                ],
                'payment_method' => 'mobile_money',
                'customer_name' => 'Jane',
                'customer_phone' => '254712345678',
                'user_id' => $user->id,
            ],
        ]);

        $callback = [
            'Body' => [
                'stkCallback' => [
                    'MerchantRequestID' => 'mr-1',
                    'CheckoutRequestID' => 'ws_CO_SUCCESS',
                    'ResultCode' => 0,
                    'ResultDesc' => 'The service request is processed successfully.',
                    'CallbackMetadata' => [
                        'Item' => [
                            ['Name' => 'Amount', 'Value' => 200],
                            ['Name' => 'MpesaReceiptNumber', 'Value' => 'NLJ7RT61SV'],
                            ['Name' => 'TransactionDate', 'Value' => 20191219102115],
                            ['Name' => 'PhoneNumber', 'Value' => 254712345678],
                        ],
                    ],
                ],
            ],
        ];

        $this->postJson(route('mpesa.callback'), $callback)->assertOk();
        $this->postJson(route('mpesa.callback'), $callback)->assertOk(); // duplicate

        $txn->refresh();
        $this->assertSame(MpesaTransaction::STATUS_SUCCESS, $txn->status, (string) $txn->result_description);
        $this->assertSame('NLJ7RT61SV', $txn->mpesa_receipt_number);
        $this->assertNotNull($txn->sale_id);
        $this->assertSame(1, Order::query()->count());

        $order = Order::query()->first();
        $this->assertSame('paid', $order->payment_status);
        $this->assertSame('mobile_money', $order->payment_method);
    }

    public function test_failed_callback_leaves_sale_unpaid(): void
    {
        $user = $this->cashier();
        $txn = MpesaTransaction::create([
            'phone_number' => '254712345678',
            'amount' => 100,
            'checkout_request_id' => 'ws_CO_FAIL',
            'status' => MpesaTransaction::STATUS_PENDING,
            'initiated_by' => $user->id,
            'checkout_payload' => ['cart' => [['product_id' => 1]], 'totals' => ['total' => 100], 'user_id' => $user->id],
        ]);

        $this->postJson(route('mpesa.callback'), [
            'Body' => [
                'stkCallback' => [
                    'CheckoutRequestID' => 'ws_CO_FAIL',
                    'ResultCode' => 1032,
                    'ResultDesc' => 'Request cancelled by user',
                ],
            ],
        ])->assertOk();

        $txn->refresh();
        $this->assertSame(MpesaTransaction::STATUS_CANCELLED, $txn->status);
        $this->assertNull($txn->sale_id);
        $this->assertSame(0, Order::query()->count());
    }

    public function test_unknown_checkout_request_id_is_acknowledged(): void
    {
        $this->postJson(route('mpesa.callback'), [
            'Body' => [
                'stkCallback' => [
                    'CheckoutRequestID' => 'ws_UNKNOWN',
                    'ResultCode' => 0,
                    'ResultDesc' => 'ok',
                ],
            ],
        ])->assertOk()->assertJson(['ResultCode' => 0]);

        $this->assertSame(0, Order::query()->count());
    }

    public function test_sale_not_marked_paid_before_callback(): void
    {
        $this->fakeDarajaSuccess();
        $user = $this->cashier();
        $this->seedCart($user);

        $this->actingAs($user)->postJson(route('admin.mpesa.stk-push'), [
            'phone' => '0712345678',
        ])->assertOk();

        $this->assertSame(0, Order::query()->where('payment_status', 'paid')->count());
        $this->assertDatabaseHas('mpesa_transactions', ['status' => 'pending', 'sale_id' => null]);
    }

    public function test_status_endpoint_returns_pending(): void
    {
        $user = $this->cashier();
        MpesaTransaction::create([
            'phone_number' => '254712345678',
            'amount' => 50,
            'checkout_request_id' => 'ws_CO_POLL',
            'status' => MpesaTransaction::STATUS_PENDING,
            'initiated_by' => $user->id,
        ]);

        $this->actingAs($user)
            ->getJson(route('admin.mpesa.status', 'ws_CO_POLL'))
            ->assertOk()
            ->assertJson(['status' => 'pending', 'is_final' => false]);
    }
}
