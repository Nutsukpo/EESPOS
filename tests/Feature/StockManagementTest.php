<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\BranchInventory;
use App\Models\Business;
use App\Models\Product;
use App\Models\ProductVariation;
use App\Models\StockBatch;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class StockManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $owner;
    protected Business $business;
    protected Branch $branch;
    protected ProductVariation $variation;

    protected function setUp(): void
    {
        parent::setUp();

        $registrar = app(PermissionRegistrar::class);
        $registrar->forgetCachedPermissions();
        $registrar->setPermissionsTeamId(null);

        foreach (['inventory.view', 'inventory.adjust'] as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => 'web',
            ]);
        }

        $this->owner = User::factory()->create();

        $this->business = Business::create([
            'owner_id' => $this->owner->id,
            'name' => 'Stock Test Business',
            'slug' => 'stock-test-business',
            'business_type' => 'retail',
            'currency' => 'GHS',
            'is_active' => true,
        ]);

        $this->business->users()->attach($this->owner->id, [
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $this->branch = Branch::create([
            'business_id' => $this->business->id,
            'name' => 'Main Branch',
            'code' => 'MAIN',
            'is_main' => true,
            'is_active' => true,
        ]);

        $product = Product::create([
            'business_id' => $this->business->id,
            'name' => 'Test Product',
            'product_type' => 'simple',
            'is_active' => true,
        ]);

        $this->variation = ProductVariation::create([
            'business_id' => $this->business->id,
            'product_id' => $product->id,
            'name' => 'Default',
            'sku' => 'STOCK-TEST-001',
            'is_active' => true,
        ]);

        $registrar->setPermissionsTeamId($this->business->id);

        $ownerRole = Role::create([
            'name' => 'Owner',
            'guard_name' => 'web',
            'business_id' => $this->business->id,
        ]);

        $ownerRole->givePermissionTo(
            Permission::whereIn('name', [
                'inventory.view',
                'inventory.adjust',
            ])->get()
        );

        $this->owner->assignRole($ownerRole);

        $registrar->setPermissionsTeamId(null);
        $registrar->forgetCachedPermissions();
    }

    protected function tearDown(): void
    {
        app(PermissionRegistrar::class)->setPermissionsTeamId(null);

        parent::tearDown();
    }

    public function test_owner_can_receive_stock_into_a_new_batch(): void
    {
        $response = $this->actingAs($this->owner)->post(
            route('business.stock.receive', $this->business),
            [
                'branch_id' => $this->branch->id,
                'product_variation_id' => $this->variation->id,
                'quantity' => '12.5000',
                'batch_number' => 'BATCH-RECEIVE-01',
                'expiry_date' => now()->addDays(60)->toDateString(),
                'notes' => 'Feature test receipt',
            ]
        );

        $response->assertRedirect();

        $batch = StockBatch::where('batch_number', 'BATCH-RECEIVE-01')
            ->firstOrFail();

        $this->assertSame('12.5000', $batch->quantity);

        $this->assertDatabaseHas('branch_inventories', [
            'business_id' => $this->business->id,
            'branch_id' => $this->branch->id,
            'product_variation_id' => $this->variation->id,
            'quantity' => '12.5000',
        ]);

        $this->assertDatabaseHas('stock_movements', [
            'stock_batch_id' => $batch->id,
            'movement_type' => 'purchase',
            'direction' => 'in',
            'quantity' => '12.5000',
        ]);
    }

    public function test_owner_can_write_off_expired_stock_and_update_inventory(): void
    {
        $inventory = BranchInventory::create([
            'business_id' => $this->business->id,
            'branch_id' => $this->branch->id,
            'product_variation_id' => $this->variation->id,
            'quantity' => 10,
        ]);

        $batch = StockBatch::create([
            'business_id' => $this->business->id,
            'branch_id' => $this->branch->id,
            'product_variation_id' => $this->variation->id,
            'batch_number' => 'EXPIRED-001',
            'expiry_date' => today()->subDay()->toDateString(),
            'received_quantity' => 10,
            'quantity' => 10,
            'received_at' => now()->subDays(30),
        ]);

        $response = $this->actingAs($this->owner)->post(
            route('business.stock.adjust', $this->business),
            [
                'branch_id' => $this->branch->id,
                'stock_batch_id' => $batch->id,
                'quantity' => '3.0000',
                'reason' => 'expired',
                'notes' => 'Expired stock test',
            ]
        );

        $response->assertRedirect();

        $this->assertSame('7.0000', $batch->fresh()->quantity);
        $this->assertSame('7.0000', $inventory->fresh()->quantity);

        $this->assertDatabaseHas('stock_movements', [
            'stock_batch_id' => $batch->id,
            'movement_type' => 'expired',
            'direction' => 'out',
            'quantity' => '3.0000',
        ]);
    }

    public function test_unexpired_batch_cannot_be_written_off_as_expired(): void
    {
        $inventory = BranchInventory::create([
            'business_id' => $this->business->id,
            'branch_id' => $this->branch->id,
            'product_variation_id' => $this->variation->id,
            'quantity' => 10,
        ]);

        $batch = StockBatch::create([
            'business_id' => $this->business->id,
            'branch_id' => $this->branch->id,
            'product_variation_id' => $this->variation->id,
            'batch_number' => 'NOT-EXPIRED-001',
            'expiry_date' => today()->addDays(30)->toDateString(),
            'received_quantity' => 10,
            'quantity' => 10,
            'received_at' => now(),
        ]);

        $response = $this->actingAs($this->owner)->post(
            route('business.stock.adjust', $this->business),
            [
                'branch_id' => $this->branch->id,
                'stock_batch_id' => $batch->id,
                'quantity' => '2',
                'reason' => 'expired',
            ]
        );

        $response->assertSessionHasErrors('reason');

        $this->assertSame('10.0000', $batch->fresh()->quantity);
        $this->assertSame('10.0000', $inventory->fresh()->quantity);
        $this->assertSame(0, StockMovement::count());
    }

    public function test_adjustment_cannot_exceed_batch_quantity(): void
    {
        $inventory = BranchInventory::create([
            'business_id' => $this->business->id,
            'branch_id' => $this->branch->id,
            'product_variation_id' => $this->variation->id,
            'quantity' => 5,
        ]);

        $batch = StockBatch::create([
            'business_id' => $this->business->id,
            'branch_id' => $this->branch->id,
            'product_variation_id' => $this->variation->id,
            'batch_number' => 'LIMIT-001',
            'received_quantity' => 5,
            'quantity' => 5,
            'received_at' => now(),
        ]);

        $response = $this->actingAs($this->owner)->post(
            route('business.stock.adjust', $this->business),
            [
                'branch_id' => $this->branch->id,
                'stock_batch_id' => $batch->id,
                'quantity' => '6',
                'reason' => 'damaged',
            ]
        );

        $response->assertSessionHasErrors('quantity');

        $this->assertSame('5.0000', $batch->fresh()->quantity);
        $this->assertSame('5.0000', $inventory->fresh()->quantity);
        $this->assertSame(0, StockMovement::count());
    }
}
