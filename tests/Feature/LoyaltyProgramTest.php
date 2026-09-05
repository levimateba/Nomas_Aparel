<?php

namespace Tests\Feature;

use App\Models\LoyaltyCard;
use App\Models\LoyaltySetting;
use App\Models\LoyaltyTransaction;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Role;
use App\Models\ShopCustomer;
use App\Models\User;
use App\Services\LoyaltyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LoyaltyProgramTest extends TestCase
{
    use RefreshDatabase;

    private function enableLoyalty(array $overrides = []): LoyaltySetting
    {
        $settings = LoyaltySetting::current();
        $settings->fill(array_merge([
            'enabled' => true,
            'amount_per_point' => 100,
            'points_awarded' => 1,
            'minimum_purchase' => 100,
            'redemption_enabled' => true,
            'redemption_points' => 100,
            'redemption_value' => 10,
            'allow_earn_on_discounted' => true,
            'allow_redemption_at_pos' => true,
            'show_on_receipt' => true,
            'reverse_points_on_refund' => true,
        ], $overrides))->save();

        return $settings->fresh();
    }

    private function adminUser(array $permissions = ['manage_loyalty', 'manage_customers', 'manage_system_settings']): User
    {
        $role = Role::query()->create([
            'name' => 'Store Manager',
            'slug' => 'manager-'.uniqid(),
            'description' => 'Test',
        ]);

        $ids = [];
        foreach ($permissions as $name) {
            $perm = Permission::query()->firstOrCreate(
                ['name' => $name],
                ['slug' => $name, 'description' => $name]
            );
            $ids[] = $perm->id;
        }
        $role->permissions()->sync($ids);

        $user = User::factory()->create([
            'is_admin' => false,
            'role_id' => $role->id,
        ]);
        if (Schema::hasTable('role_user')) {
            $user->roles()->sync([$role->id]);
        }

        return $user;
    }

    public function test_loyalty_settings_save_correctly(): void
    {
        $user = $this->adminUser();
        $this->actingAs($user)
            ->put(route('admin.loyalty.settings.update'), [
                'enabled' => '1',
                'amount_per_point' => 50,
                'points_awarded' => 2,
                'minimum_purchase' => 200,
                'redemption_enabled' => '1',
                'redemption_points' => 50,
                'redemption_value' => 5,
                'points_expiration_days' => 180,
            ])
            ->assertRedirect();

        $settings = LoyaltySetting::current();
        $this->assertTrue($settings->enabled);
        $this->assertEquals(50, (float) $settings->amount_per_point);
        $this->assertEquals(2, (int) $settings->points_awarded);
    }

    public function test_customer_can_receive_loyalty_card_and_not_duplicate_active(): void
    {
        $this->enableLoyalty();
        $customer = ShopCustomer::query()->create(['name' => 'John Kamau', 'is_active' => true]);
        $service = app(LoyaltyService::class);

        $card = $service->createLoyaltyCard($customer);
        $this->assertStringStartsWith('NOM-', $card->card_number);
        $this->assertEquals(0, $card->points_balance);

        $this->expectException(ValidationException::class);
        $service->createLoyaltyCard($customer);
    }

    public function test_points_calculation_and_minimum_purchase(): void
    {
        $this->enableLoyalty(['minimum_purchase' => 100]);
        $service = app(LoyaltyService::class);

        $this->assertEquals(27, $service->calculateEarnedPoints(2750));
        $this->assertEquals(0, $service->calculateEarnedPoints(99));
        $this->assertEquals(1, $service->calculateEarnedPoints(100));
    }

    public function test_points_not_awarded_when_disabled(): void
    {
        $this->enableLoyalty(['enabled' => false]);
        $this->assertEquals(0, app(LoyaltyService::class)->calculateEarnedPoints(5000));
    }

    public function test_earn_creates_ledger_with_sale_reference(): void
    {
        $this->enableLoyalty();
        $customer = ShopCustomer::query()->create(['name' => 'Jane', 'is_active' => true]);
        $service = app(LoyaltyService::class);
        $service->createLoyaltyCard($customer);

        $order = Order::query()->create([
            'order_number' => 'POS-TEST-1',
            'customer_name' => 'Jane',
            'customer_email' => 'jane@test.local',
            'shipping_address' => 'In-store',
            'status' => 'paid',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'total_amount' => 1000,
            'shop_customer_id' => $customer->id,
        ]);

        $earned = $service->earnPoints($customer, $order, 10, null);
        $this->assertNotNull($earned);
        $this->assertEquals(10, $earned->balance_after);
        $this->assertEquals($order->id, $earned->order_id);
        $this->assertEquals(LoyaltyTransaction::TYPE_EARNED, $earned->type);
    }

    public function test_redeem_respects_balance_and_creates_ledger(): void
    {
        $this->enableLoyalty();
        $customer = ShopCustomer::query()->create(['name' => 'Redeem User', 'is_active' => true]);
        $service = app(LoyaltyService::class);
        $card = $service->createLoyaltyCard($customer);
        $card->update(['points_balance' => 345]);

        $order = Order::query()->create([
            'order_number' => 'POS-TEST-2',
            'customer_name' => 'Redeem User',
            'customer_email' => 'r@test.local',
            'shipping_address' => 'In-store',
            'status' => 'paid',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'total_amount' => 500,
            'shop_customer_id' => $customer->id,
        ]);

        $tx = $service->redeemPoints($customer, $order, 100, 10.0, null);
        $this->assertEquals(-100, $tx->points);
        $this->assertEquals(245, $tx->balance_after);
        $this->assertEquals($order->id, $tx->order_id);
        $this->assertEquals(245, $card->fresh()->points_balance);
    }

    public function test_cannot_redeem_more_than_balance(): void
    {
        $this->enableLoyalty();
        $customer = ShopCustomer::query()->create(['name' => 'Low Balance', 'is_active' => true]);
        $service = app(LoyaltyService::class);
        $card = $service->createLoyaltyCard($customer);
        $card->update(['points_balance' => 50]);

        $order = Order::query()->create([
            'order_number' => 'POS-TEST-3',
            'customer_name' => 'Low',
            'customer_email' => 'l@test.local',
            'shipping_address' => 'In-store',
            'status' => 'paid',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'total_amount' => 100,
            'shop_customer_id' => $customer->id,
        ]);

        $this->expectException(ValidationException::class);
        $service->redeemPoints($customer, $order, 100, 10.0, null);
    }

    public function test_manual_adjustment_creates_ledger(): void
    {
        $this->enableLoyalty();
        $customer = ShopCustomer::query()->create(['name' => 'Adj', 'is_active' => true]);
        $service = app(LoyaltyService::class);
        $service->createLoyaltyCard($customer);

        $tx = $service->adjustPoints($customer, 50, 'Customer promotion', null);
        $this->assertEquals(LoyaltyTransaction::TYPE_ADJUSTMENT, $tx->type);
        $this->assertEquals(50, $tx->balance_after);
    }

    public function test_unauthorized_user_cannot_modify_settings(): void
    {
        $user = $this->adminUser(['manage_customers']);
        $this->actingAs($user)
            ->put(route('admin.loyalty.settings.update'), [
                'amount_per_point' => 100,
                'points_awarded' => 1,
                'minimum_purchase' => 100,
                'redemption_points' => 100,
                'redemption_value' => 10,
                'points_expiration_days' => 365,
            ])
            ->assertForbidden();
    }

    public function test_concurrent_redemptions_do_not_corrupt_balance(): void
    {
        $this->enableLoyalty();
        $customer = ShopCustomer::query()->create(['name' => 'Race', 'is_active' => true]);
        $service = app(LoyaltyService::class);
        $card = $service->createLoyaltyCard($customer);
        $card->update(['points_balance' => 100]);

        $makeOrder = fn (string $n) => Order::query()->create([
            'order_number' => $n,
            'customer_name' => 'Race',
            'customer_email' => 'race@test.local',
            'shipping_address' => 'In-store',
            'status' => 'paid',
            'payment_method' => 'cash',
            'payment_status' => 'paid',
            'total_amount' => 50,
            'shop_customer_id' => $customer->id,
        ]);

        $orderA = $makeOrder('POS-RACE-A');
        $orderB = $makeOrder('POS-RACE-B');

        $service->redeemPoints($customer, $orderA, 100, 10.0, null);

        try {
            $service->redeemPoints($customer, $orderB, 100, 10.0, null);
            $this->fail('Second redemption should fail');
        } catch (ValidationException $e) {
            $this->assertTrue(true);
        }

        $this->assertEquals(0, $card->fresh()->points_balance);
        $this->assertEquals(1, LoyaltyTransaction::query()->where('type', 'redeemed')->count());
    }
}
