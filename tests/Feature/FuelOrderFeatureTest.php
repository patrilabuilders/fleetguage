<?php

namespace Tests\Feature;

use App\AppServiceProvider;
use App\Livewire\CreateFuelOrder;
use App\Models\Asset;
use App\Models\AssetType;
use App\Models\ChargeableAccount;
use App\Models\Company;
use App\Models\FuelOrder;
use App\Models\SubAccount;
use App\Models\SubAccountBudget;
use App\Models\User;
use App\Models\UtilizationEntry;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FuelOrderFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_data_logger_can_access_fuel_order_routes()
    {
        $user = User::factory()->create(['role' => 'data_logger']);

        $response = $this->actingAs($user)->get(route('fuel-orders.index'));
        $response->assertStatus(200);

        $response = $this->actingAs($user)->get(route('fuel-orders.create'));
        $response->assertStatus(200);
    }

    public function test_standard_user_cannot_access_create_fuel_order_route()
    {
        $user = User::factory()->create(['role' => 'moderator']);

        $response = $this->actingAs($user)->get(route('fuel-orders.create'));
        $response->assertStatus(403);
    }

    public function test_livewire_component_calculates_correctly_for_kilometer_reading()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);
        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'tank_capacity' => 100,
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'reference' => 'REF-001',
            'particulars' => 'Start',
            'start_kilometer_reading' => 1000,
            'end_kilometer_reading' => 1100, // 100 km diff
            'start_hour_reading' => 100,
            'end_hour_reading' => 100,
            'driver_operator_name' => 'John Doe',
            'chargeable_account_id' => $account->id,
            'calculation_type' => 'Kilometer Reading',
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-02',
            'start_time' => '09:00', // Different start time
            'end_time' => '17:00',
            'reference' => 'REF-002',
            'particulars' => 'End',
            'start_kilometer_reading' => 1200,
            'end_kilometer_reading' => 1300, // 100 km diff
            'start_hour_reading' => 110,
            'end_hour_reading' => 110,
            'driver_operator_name' => 'John Doe',
            'chargeable_account_id' => $account->id,
            'calculation_type' => 'Kilometer Reading',
        ]);

        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', $asset->id)
            ->set('date_from', '2026-03-01')
            ->set('date_to', '2026-03-02')
            ->assertSet('unprocessed_entries_count', 2)
            ->assertSet('calculated_quantity', 80) // 200 / 2.5 = 80
            ->set('say_quantity', 80)
            ->call('submit')
            ->assertRedirect(route('fuel-orders.index'));

        $this->assertDatabaseHas('fuel_orders', [
            'asset_id' => $asset->id,
            'calculated_quantity' => 80,
            'say_quantity' => 80,
            'calculated_kilometers' => 200,
            'calculated_hours' => 0,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'date_from' => '2026-03-01',
            'date_to' => '2026-03-02',
            'status' => 'PEND',
            'actual_quantity' => 0,
        ]);

        $this->assertEquals(2, UtilizationEntry::whereNotNull('fuel_order_id')->count());
        $this->assertDatabaseHas('utilization_entries', [
            'asset_id' => $asset->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'calculation_type' => 'Kilometer Reading',
        ]);
    }

    public function test_livewire_component_groups_totals_by_chargeable_account()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account1 = ChargeableAccount::create(['name' => 'Account A', 'status' => 'Active']);
        $account2 = ChargeableAccount::create(['name' => 'Account B', 'status' => 'Active']);
        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2,
            'tank_capacity' => 100,
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '12:00',
            'reference' => 'REF-A1',
            'particulars' => 'Work A',
            'start_kilometer_reading' => 1000,
            'end_kilometer_reading' => 1050,
            'driver_operator_name' => 'John Doe',
            'chargeable_account_id' => $account1->id,
            'calculation_type' => 'Kilometer Reading',
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'reference' => 'REF-001',
            'start_time' => '13:00',
            'end_time' => '17:00',
            'reference' => 'REF-B1',
            'particulars' => 'Work B',
            'start_kilometer_reading' => 1050,
            'end_kilometer_reading' => 1080,
            'driver_operator_name' => 'John Doe',
            'chargeable_account_id' => $account2->id,
            'calculation_type' => 'Kilometer Reading',
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-02',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'reference' => 'REF-A2',
            'particulars' => 'Work A2',
            'start_kilometer_reading' => 1080,
            'end_kilometer_reading' => 1100,
            'driver_operator_name' => 'John Doe',
            'chargeable_account_id' => $account1->id,
            'calculation_type' => 'Kilometer Reading',
        ]);

        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', $asset->id)
            ->set('date_from', '2026-03-01')
            ->set('date_to', '2026-03-02')
            ->assertSet('unprocessed_entries_count', 3)
            ->assertSet('calculated_kilometers', 100)
            ->assertSet('calculated_quantity', 50)
            ->assertSet('grouped_totals', [
                'Account A' => [
                    'kilometers' => 70.0, // (1050-1000) + (1100-1080) = 50 + 20
                    'hours' => 0.0,
                    'quantity' => 35.0, // 70 / 2 = 35
                    'remaining' => 0.0,
                    'balance' => -35.0,
                ],
                'Account B' => [
                    'kilometers' => 30.0, // (1080-1050) = 30
                    'hours' => 0.0,
                    'quantity' => 15.0, // 30 / 2 = 15
                    'remaining' => 0.0,
                    'balance' => -15.0,
                ],
            ]);
    }

    public function test_livewire_component_calculates_correctly_for_actual_operation_time()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $type = AssetType::create(['name' => 'Generator']);
        $account = ChargeableAccount::create(['name' => 'Maintenance Dept', 'status' => 'Active']);
        $asset = Asset::create([
            'fleet_no' => 'G-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 0,
            'fuel_factor_hr' => 5, // 5 liters per hour
            'tank_capacity' => 100,
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '08:00', // 0 hours difference
            'reference' => 'REF-001',
            'particulars' => 'Start',
            'start_kilometer_reading' => 0,
            'end_kilometer_reading' => 0,
            'start_hour_reading' => 100,
            'end_hour_reading' => 100,
            'driver_operator_name' => 'Jane Doe',
            'chargeable_account_id' => $account->id,
            'calculation_type' => 'Timeframe',
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'reference' => 'REF-001',
            'start_time' => '11:00', // Changed from 08:00
            'end_time' => '13:30', // 2.5 hours difference
            'reference' => 'REF-003',
            'particulars' => 'End',
            'start_kilometer_reading' => 0,
            'end_kilometer_reading' => 0,
            'start_hour_reading' => 102.5,
            'end_hour_reading' => 102.5,
            'driver_operator_name' => 'Jane Doe',
            'chargeable_account_id' => $account->id,
            'calculation_type' => 'Timeframe',
        ]);

        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', $asset->id)
            ->set('date_from', '2026-03-01')
            ->set('date_to', '2026-03-01')
            ->assertSet('unprocessed_entries_count', 2)
            ->assertSet('calculated_quantity', 12.5) // 2.5 hours * 5
            ->set('say_quantity', 13)
            ->call('submit')
            ->assertRedirect(route('fuel-orders.index'));

        $this->assertDatabaseHas('fuel_orders', [
            'asset_id' => $asset->id,
            'say_quantity' => 13,
            'calculated_quantity' => 12.5,
            'calculated_hours' => 2.5,
            'calculated_kilometers' => 0,
            'fuel_factor_km' => 0,
            'fuel_factor_hr' => 5,
            'date_from' => '2026-03-01',
            'date_to' => '2026-03-01',
            'status' => 'PEND',
            'actual_quantity' => 0,
        ]);
    }

    public function test_unprocessed_entries_contains_unbudgeted_instead_of_reference()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);
        $asset = Asset::create([
            'fleet_no' => 'V-TEST',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'tank_capacity' => 100,
        ]);

        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '10:00',
            'reference' => 'REF-TEST',
            'particulars' => 'Test',
            'start_kilometer_reading' => 1000,
            'end_kilometer_reading' => 1100,
            'driver_operator_name' => 'Tester',
            'chargeable_account_id' => $account->id,
            'calculation_type' => 'Kilometer Reading',
            'unbudgeted' => true,
        ]);

        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', $asset->id)
            ->set('date_from', '2026-03-01')
            ->set('date_to', '2026-03-01')
            ->assertSet('unprocessed_entries_count', 1)
            ->assertViewHas('unprocessed_entries', function ($entries) {
                return count($entries) === 1 &&
                       $entries[0]['unbudgeted'] === true &&
                       ! isset($entries[0]['reference']);
            });
    }

    public function test_user_can_actualize_fuel_order()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $user = User::factory()->create(['role' => 'administrator']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'tank_capacity' => 100,
        ]);

        $fuelOrder = FuelOrder::create([
            'asset_id' => $asset->id,
            'calculated_quantity' => 80,
            'say_quantity' => 80,
            'status' => 'PEND',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->post(route('fuel-orders.store-actualization', $fuelOrder), [
            'actual_quantity' => 495.5,
        ]);

        $response->assertRedirect(route('fuel-orders.index'));
        $this->assertDatabaseHas('fuel_orders', [
            'id' => $fuelOrder->id,
            'say_quantity' => 80.0,
            'actual_quantity' => 495.5,
            'status' => 'DONE',
        ]);
    }

    public function test_administrator_can_edit_fuel_order()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $user = User::factory()->create(['role' => 'administrator']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'tank_capacity' => 100,
        ]);

        $fuelOrder = FuelOrder::create([
            'asset_id' => $asset->id,
            'calculated_quantity' => 80,
            'say_quantity' => 80,
            'status' => 'PEND',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->put(route('fuel-orders.update', $fuelOrder), [
            'say_quantity' => 90.5,
            'actual_quantity' => 85.0,
            'status' => 'DONE',
        ]);

        $response->assertRedirect(route('fuel-orders.index'));
        $this->assertDatabaseHas('fuel_orders', [
            'id' => $fuelOrder->id,
            'say_quantity' => 90.5,
            'actual_quantity' => 85.0,
            'status' => 'DONE',
        ]);
    }

    public function test_updating_fuel_order_to_void_releases_utilization_entries()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $user = User::factory()->create(['role' => 'administrator']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account = ChargeableAccount::create(['name' => 'Account A', 'status' => 'Active']);

        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 0,
            'tank_capacity' => 100,
        ]);

        $fuelOrder = FuelOrder::create([
            'asset_id' => $asset->id,
            'calculated_quantity' => 80,
            'say_quantity' => 80,
            'status' => 'PEND',
            'created_by' => $user->id,
        ]);

        $entry = UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'reference' => 'REF-VOID',
            'particulars' => 'Test',
            'driver_operator_name' => 'John',
            'start_kilometer_reading' => 1000,
            'end_kilometer_reading' => 1100,
            'calculation_type' => 'Kilometer Reading',
            'chargeable_account_id' => $account->id,
            'fuel_order_id' => $fuelOrder->id,
        ]);

        $this->assertEquals($fuelOrder->id, $entry->fresh()->fuel_order_id);

        $response = $this->actingAs($user)->post(route('fuel-orders.void', $fuelOrder), [
            'void_remarks' => 'Test void remarks',
        ]);

        $response->assertRedirect(route('fuel-orders.index'));

        $this->assertDatabaseHas('fuel_orders', [
            'id' => $fuelOrder->id,
            'status' => 'VOID',
            'void_remarks' => 'Test void remarks',
        ]);

        $this->assertNull($entry->fresh()->fuel_order_id);
    }

    public function test_can_create_direct_fuel_order_without_asset()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $account = ChargeableAccount::create(['name' => 'General Overhead', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Admin Ops']);

        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', null)
            ->set('chargeable_account_id', $account->id)
            ->set('sub_account_id', $subAccount->id)
            ->set('remarks', 'Monthly generator backup fuel replenishment')
            ->set('say_quantity', 150)
            ->call('submit')
            ->assertRedirect(route('fuel-orders.index'));

        $this->assertDatabaseHas('fuel_orders', [
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'unbudgeted' => false,
            'remarks' => 'Monthly generator backup fuel replenishment',
            'say_quantity' => 150,
            'calculated_quantity' => 0,
            'calculated_hours' => 0,
            'calculated_kilometers' => 0,
            'status' => 'PEND',
        ]);
    }

    public function test_direct_fuel_order_requires_remarks_and_chargeable_account()
    {
        $user = User::factory()->create(['role' => 'data_logger']);

        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', null)
            ->set('say_quantity', 150)
            ->call('submit')
            ->assertHasErrors([
                'chargeable_account_id' => 'required_without',
                'sub_account_id' => 'required_without',
                'remarks' => 'required_without',
            ]);
    }

    public function test_show_fuel_order_displays_fuel_type_in_say_quantity_when_asset_is_present()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);
        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_type' => 'Diesel',
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'tank_capacity' => 100,
        ]);

        $fuelOrder = FuelOrder::create([
            'asset_id' => $asset->id,
            'chargeable_account_id' => $account->id,
            'calculated_quantity' => 100,
            'say_quantity' => 100,
            'status' => 'PEND',
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('fuel-orders.show', $fuelOrder));

        $response->assertStatus(200);
        $response->assertSee('Say Fuel Quantity (Diesel):');
    }

    public function test_show_fuel_order_does_not_display_fuel_type_in_say_quantity_for_direct_orders()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);

        $fuelOrder = FuelOrder::create([
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'calculated_quantity' => 0,
            'say_quantity' => 100,
            'status' => 'PEND',
            'created_by' => $user->id,
            'remarks' => 'Monthly generator replenishment',
        ]);

        $response = $this->actingAs($user)->get(route('fuel-orders.show', $fuelOrder));

        $response->assertStatus(200);
        $response->assertSee('Say Fuel Quantity:');
        $response->assertDontSee('Say Fuel Quantity (');
    }

    public function test_show_fuel_order_displays_issue_date_and_order_number_in_printed_view()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);

        $fuelOrder = FuelOrder::create([
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'calculated_quantity' => 0,
            'say_quantity' => 100,
            'status' => 'PEND',
            'created_by' => $user->id,
            'remarks' => 'Monthly generator replenishment',
        ]);

        // Request printed view by adding print=1 query param
        $response = $this->actingAs($user)->get(route('fuel-orders.show', $fuelOrder).'?print=1');

        $response->assertStatus(200);
        $response->assertSee('Issue Date:');
        $response->assertSee('Order Number: #'.str_pad($fuelOrder->id, 5, '0', STR_PAD_LEFT));
    }

    public function test_direct_fuel_order_over_budget_requires_waiver()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Alpha']);

        // Allocate budget of 50.00 L
        $subAccount->budgets()->create([
            'budget_quantity' => 50.00,
            'status' => 'Approved',
            'created_by' => $user->id,
        ]);

        // Request 100.00 L (exceeds budget by 50.00 L)
        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', null)
            ->set('chargeable_account_id', $account->id)
            ->set('sub_account_id', $subAccount->id)
            ->set('remarks', 'Direct replenishment')
            ->set('say_quantity', 100)
            ->call('submit');

        $this->assertDatabaseHas('fuel_orders', [
            'sub_account_id' => $subAccount->id,
            'say_quantity' => 100.0,
            'is_waiver_pending' => true,
        ]);
    }

    public function test_only_administrator_can_approve_waiver()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $company = Company::factory()->create();
        $user = User::factory()->create(['company_id' => $company->id, 'role' => 'data_logger']);
        $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'administrator']);
        $account = ChargeableAccount::create([
            'company_id' => $company->id,
            'name' => 'Project Alpha',
            'status' => 'Active',
        ]);
        $subAccount = $account->subAccounts()->create(['company_id' => $company->id, 'name' => 'Sub Alpha']);

        $fuelOrder = FuelOrder::create([
            'company_id' => $company->id,
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'say_quantity' => 100,
            'status' => 'PEND',
            'is_waiver_pending' => true,
            'created_by' => $user->id,
            'remarks' => 'Direct replenishment',
        ]);

        // Attempt as data_logger (non-admin)
        $response = $this->actingAs($user)->post(route('fuel-orders.approve-waiver', $fuelOrder));
        $response->assertStatus(403);
        $this->assertTrue($fuelOrder->fresh()->is_waiver_pending);

        // Attempt as administrator
        $response = $this->actingAs($admin)->post(route('fuel-orders.approve-waiver', $fuelOrder));
        $response->assertRedirect();
        $this->assertFalse($fuelOrder->fresh()->is_waiver_pending);
        $this->assertEquals($admin->id, $fuelOrder->fresh()->waived_by);
    }

    public function test_actualization_is_blocked_for_fuel_orders_with_pending_waivers()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $user = User::factory()->create(['role' => 'fuel_man']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Alpha']);

        $fuelOrder = FuelOrder::create([
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'say_quantity' => 100,
            'status' => 'PEND',
            'is_waiver_pending' => true,
            'created_by' => $user->id,
            'remarks' => 'Direct replenishment',
        ]);

        // Try to access actualize form
        $response = $this->actingAs($user)->get(route('fuel-orders.actualize', $fuelOrder));
        $response->assertRedirect(route('fuel-orders.index'));
        $response->assertSessionHas('error', 'This fuel order has a pending budget waiver and cannot be actualized.');

        // Try to submit actualization
        $response = $this->actingAs($user)->post(route('fuel-orders.store-actualization', $fuelOrder), [
            'actual_quantity' => 100,
        ]);
        $response->assertRedirect(route('fuel-orders.index'));
        $response->assertSessionHas('error', 'This fuel order has a pending budget waiver and cannot be actualized.');
    }

    public function test_printing_is_blocked_for_fuel_orders_with_pending_waivers()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Alpha']);

        $fuelOrder = FuelOrder::create([
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'say_quantity' => 100,
            'status' => 'PEND',
            'is_waiver_pending' => true,
            'created_by' => $user->id,
            'remarks' => 'Direct replenishment',
        ]);

        // Access web view and assert Print button is NOT visible
        $response = $this->actingAs($user)->get(route('fuel-orders.show', $fuelOrder));
        $response->assertStatus(200);
        $response->assertDontSee('?print=1');

        // Try to access print view directly with print=1
        $response = $this->actingAs($user)->get(route('fuel-orders.show', $fuelOrder).'?print=1');
        $response->assertStatus(403);
    }

    public function test_create_fuel_order_tracks_negative_balance_and_sets_has_negative_balance_true_for_asset()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Alpha']);

        // Approve a very small budget of 2.0 Liters
        $subAccount->budgets()->create([
            'budget_quantity' => 2.0,
            'status' => 'Approved',
            'allocated_by' => $user->id,
        ]);

        $asset = Asset::create([
            'fleet_no' => 'V-001',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'tank_capacity' => 100,
        ]);

        // Create a utilization entry requiring 10.0 Liters
        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'date' => '2026-06-15',
            'start_time' => '08:00',
            'end_time' => '18:00', // 10 hours * 1.5 L/hr = 15 L requested
            'fuel_factor_hr' => 1.5,
            'calculation_type' => 'Timeframe',
            'particulars' => 'Some particulars',
            'driver_operator_name' => 'Operator A',
            'reference' => 'REF-001',
        ]);

        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', $asset->id)
            ->set('date_from', '2026-06-15')
            ->set('date_to', '2026-06-15')
            ->assertSet('has_negative_balance', true)
            ->assertSet('grouped_totals', [
                'Project Alpha - Sub Alpha' => [
                    'kilometers' => 0.0,
                    'hours' => 10.0,
                    'quantity' => 15.0,
                    'remaining' => 2.0,
                    'balance' => -13.0,
                ],
            ]);
    }

    public function test_create_fuel_order_tracks_negative_balance_and_sets_has_negative_balance_true_for_direct_order()
    {
        $user = User::factory()->create(['role' => 'data_logger']);
        $account = ChargeableAccount::create(['name' => 'Project Alpha', 'status' => 'Active']);
        $subAccount = $account->subAccounts()->create(['name' => 'Sub Alpha']);

        // Approve a very small budget of 2.0 Liters
        $subAccount->budgets()->create([
            'budget_quantity' => 2.0,
            'status' => 'Approved',
            'allocated_by' => $user->id,
        ]);

        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('asset_id', null)
            ->set('chargeable_account_id', $account->id)
            ->set('sub_account_id', $subAccount->id)
            ->set('say_quantity', 15.0)
            ->assertSet('has_negative_balance', true);
    }

    public function test_show_fuel_order_displays_remaining_and_balance_in_breakdown_by_charged_to()
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $assetType = AssetType::create(['name' => 'Vehicle']);

        $account = ChargeableAccount::create([
            'name' => 'Project Alpha',
            'classification' => 'Running',
            'status' => 'Active',
        ]);

        $subAccount = SubAccount::create([
            'chargeable_account_id' => $account->id,
            'name' => 'Sub X',
        ]);

        // Allocate budget of 500 liters
        SubAccountBudget::create([
            'sub_account_id' => $subAccount->id,
            'budget_quantity' => 500,
            'status' => 'Approved',
            'allocated_by' => $user->id,
        ]);

        $asset = Asset::create([
            'fleet_no' => 'V-100',
            'asset_type_id' => $assetType->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 2.0,
            'tank_capacity' => 100,
        ]);

        $fuelOrder = FuelOrder::create([
            'asset_id' => $asset->id,
            'calculated_quantity' => 4.0,
            'say_quantity' => 4,
            'status' => 'PEND',
            'created_by' => $user->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 2.0,
        ]);

        // Create a utilization entry for this fuel order
        $entry = UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-08-15',
            'start_time' => '08:00',
            'end_time' => '10:00', // 2 hours * 2.0 = 4.0 L
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $subAccount->id,
            'reference' => 'REF-123',
            'calculation_type' => 'Timeframe',
            'particulars' => 'Daily run',
            'fuel_order_id' => $fuelOrder->id,
            'fuel_factor_hr' => 2.0,
            'fuel_factor_km' => 2.5,
        ]);

        $response = $this->actingAs($user)->get(route('fuel-orders.show', $fuelOrder));

        $response->assertStatus(200);
        $response->assertSee('Remaining (L)');
        $response->assertSee('Balance (L)');

        // Remaining budget before order should be 500.00
        $response->assertSee('500.00');
        // Balance after order (500 - 4.00) should be 496.00
        $response->assertSee('496.00');
    }

    public function test_edit_direct_fuel_order_requires_and_updates_sub_account()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'General Overhead', 'status' => 'Active']);
        $sub1 = $account->subAccounts()->create(['name' => 'Sub One']);
        $sub2 = $account->subAccounts()->create(['name' => 'Sub Two']);

        $fuelOrder = FuelOrder::create([
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub1->id,
            'say_quantity' => 150,
            'actual_quantity' => 150,
            'status' => 'PEND',
            'created_by' => $user->id,
        ]);

        // 1. Sending no sub_account_id returns validation error
        $response = $this->actingAs($user)->put(route('fuel-orders.update', $fuelOrder), [
            'say_quantity' => 200,
            'actual_quantity' => 200,
            'status' => 'PEND',
            'sub_account_id' => '',
        ]);

        $response->assertSessionHasErrors(['sub_account_id']);

        // 2. Sending valid sub_account_id updates correctly
        $response = $this->actingAs($user)->put(route('fuel-orders.update', $fuelOrder), [
            'say_quantity' => 200,
            'actual_quantity' => 200,
            'status' => 'PEND',
            'sub_account_id' => $sub2->id,
        ]);

        $response->assertSessionHasNoErrors();
        $response->assertRedirect(route('fuel-orders.index'));

        $this->assertEquals($sub2->id, $fuelOrder->fresh()->sub_account_id);
    }

    public function test_fuel_orders_index_filters_by_status_properly()
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'General Overhead', 'status' => 'Active']);
        $sub = $account->subAccounts()->create(['name' => 'Sub One']);

        // 1. Create a PEND order without pending waiver
        $pendOrder = FuelOrder::create([
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'say_quantity' => 100,
            'status' => 'PEND',
            'is_waiver_pending' => false,
            'created_by' => $user->id,
        ]);

        // 2. Create a PEND order with pending waiver (PENDING_WAIVER)
        $waiverOrder = FuelOrder::create([
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'say_quantity' => 150,
            'status' => 'PEND',
            'is_waiver_pending' => true,
            'created_by' => $user->id,
        ]);

        // 3. Create a DONE order
        $doneOrder = FuelOrder::create([
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'say_quantity' => 200,
            'status' => 'DONE',
            'is_waiver_pending' => false,
            'created_by' => $user->id,
        ]);

        // 4. Create a VOID order
        $voidOrder = FuelOrder::create([
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'say_quantity' => 250,
            'status' => 'VOID',
            'is_waiver_pending' => false,
            'created_by' => $user->id,
        ]);

        // Filter by All (no status query) should show all orders
        $response = $this->actingAs($user)->get(route('fuel-orders.index'));
        $response->assertStatus(200);
        $response->assertSee('#'.str_pad($pendOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertSee('#'.str_pad($waiverOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertSee('#'.str_pad($doneOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertSee('#'.str_pad($voidOrder->id, 5, '0', STR_PAD_LEFT));

        // Filter by PEND (excluding pending waiver)
        $response = $this->actingAs($user)->get(route('fuel-orders.index', ['status' => 'PEND']));
        $response->assertStatus(200);
        $response->assertSee('#'.str_pad($pendOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertDontSee('#'.str_pad($waiverOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertDontSee('#'.str_pad($doneOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertDontSee('#'.str_pad($voidOrder->id, 5, '0', STR_PAD_LEFT));

        // Filter by PENDING_WAIVER
        $response = $this->actingAs($user)->get(route('fuel-orders.index', ['status' => 'PENDING_WAIVER']));
        $response->assertStatus(200);
        $response->assertDontSee('#'.str_pad($pendOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertSee('#'.str_pad($waiverOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertDontSee('#'.str_pad($doneOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertDontSee('#'.str_pad($voidOrder->id, 5, '0', STR_PAD_LEFT));

        // Filter by DONE
        $response = $this->actingAs($user)->get(route('fuel-orders.index', ['status' => 'DONE']));
        $response->assertStatus(200);
        $response->assertDontSee('#'.str_pad($pendOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertDontSee('#'.str_pad($waiverOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertSee('#'.str_pad($doneOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertDontSee('#'.str_pad($voidOrder->id, 5, '0', STR_PAD_LEFT));

        // Filter by VOID
        $response = $this->actingAs($user)->get(route('fuel-orders.index', ['status' => 'VOID']));
        $response->assertStatus(200);
        $response->assertDontSee('#'.str_pad($pendOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertDontSee('#'.str_pad($waiverOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertDontSee('#'.str_pad($doneOrder->id, 5, '0', STR_PAD_LEFT));
        $response->assertSee('#'.str_pad($voidOrder->id, 5, '0', STR_PAD_LEFT));
    }

    public function test_fuel_orders_index_does_not_filter_by_sub_account_and_view_does_not_receive_sub_accounts()
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'General Overhead', 'status' => 'Active']);
        $sub1 = $account->subAccounts()->create(['name' => 'Sub One']);
        $sub2 = $account->subAccounts()->create(['name' => 'Sub Two']);

        // Create order for sub1
        $order1 = FuelOrder::create([
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub1->id,
            'say_quantity' => 100,
            'status' => 'PEND',
            'is_waiver_pending' => false,
            'created_by' => $user->id,
        ]);

        // Create order for sub2
        $order2 = FuelOrder::create([
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub2->id,
            'say_quantity' => 200,
            'status' => 'PEND',
            'is_waiver_pending' => false,
            'created_by' => $user->id,
        ]);

        // Access index page passing sub_account_id filter - it should ignore it and show both orders
        $response = $this->actingAs($user)->get(route('fuel-orders.index', ['sub_account_id' => $sub1->id]));
        $response->assertStatus(200);
        $response->assertSee('#'.str_pad($order1->id, 5, '0', STR_PAD_LEFT));
        $response->assertSee('#'.str_pad($order2->id, 5, '0', STR_PAD_LEFT));

        // Assert that the 'subAccounts' variable is NOT passed to the view
        $response->assertViewMissing('subAccounts');
    }

    public function test_fuel_orders_create_shows_step_1_by_default()
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $response = $this->actingAs($user)->get(route('fuel-orders.create'));
        $response->assertStatus(200);
        $response->assertSee('Choose Fuel Order Creation Method');
    }

    public function test_fuel_orders_create_wizard_navigation_and_reset()
    {
        $user = User::factory()->create(['role' => 'administrator']);
        Livewire::actingAs($user)
            ->test(AppServiceProvider::class === null ? null : CreateFuelOrder::class)
            ->assertSet('creation_method', '')
            ->call('setCreationMethod', 'with_asset')
            ->assertSet('creation_method', 'with_asset')
            ->call('resetCreationMethod')
            ->assertSet('creation_method', '');
    }

    public function test_fuel_orders_print_page_loads_and_displays_data()
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $account = ChargeableAccount::create(['name' => 'General Overhead', 'status' => 'Active']);
        $sub = $account->subAccounts()->create(['name' => 'Sub One']);

        $order = FuelOrder::create([
            'asset_id' => null,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'say_quantity' => 125.50,
            'status' => 'PEND',
            'is_waiver_pending' => false,
            'created_by' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('fuel-orders.print', ['chargeable_account_id' => $account->id]));
        $response->assertStatus(200);
        $response->assertSee('Fuel Orders List');
        $response->assertSee('#'.str_pad($order->id, 5, '0', STR_PAD_LEFT));
        $response->assertSee('125.50 L');
    }

    public function test_fuel_orders_show_breakdown_by_charged_to_rows_are_clickable()
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account = ChargeableAccount::create(['name' => 'General Overhead', 'status' => 'Active']);
        $sub = $account->subAccounts()->create(['name' => 'Sub One']);
        $asset = Asset::create([
            'fleet_no' => 'V-101',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'tank_capacity' => 100,
        ]);

        $fuelOrder = FuelOrder::create([
            'asset_id' => $asset->id,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'say_quantity' => 100,
            'status' => 'PEND',
            'is_waiver_pending' => false,
            'created_by' => $user->id,
        ]);

        // Add a utilization entry so it has breakdown rows
        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'reference' => 'REF-001',
            'particulars' => 'Daily Operation',
            'start_kilometer_reading' => 1000,
            'end_kilometer_reading' => 1100, // 100 km diff
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'calculation_type' => 'Kilometer Reading',
            'fuel_order_id' => $fuelOrder->id,
        ]);

        $response = $this->actingAs($user)->get(route('fuel-orders.show', $fuelOrder));
        $response->assertStatus(200);

        // Expected URL that row should point to
        $expectedUrl = route('utilization-entries.index', [
            'fuel_order_id' => $fuelOrder->id,
            'sub_account_id' => $sub->id,
        ]);

        // Check if the markup has the onclick attribute pointing to that url and style pointer
        $response->assertSee('onclick="window.open(\''.$expectedUrl.'\', \'_blank\')"', false);
        $response->assertSee('style="cursor: pointer;"', false);
    }

    public function test_uncontrolled_sub_accounts_bypass_overbudget_waiver_controls()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $user = User::factory()->create(['role' => 'administrator']);

        $account = ChargeableAccount::create(['name' => 'General Overhead', 'status' => 'Active']);

        // 1. Controlled sub-account with some budget
        $controlledSub = $account->subAccounts()->create(['name' => 'Controlled Sub', 'type' => 'Controlled']);
        $controlledSub->budgets()->create([
            'budget_quantity' => 100.0,
            'status' => 'Approved',
            'created_by' => $user->id,
        ]);

        // 2. Uncontrolled sub-account with some budget
        $uncontrolledSub = $account->subAccounts()->create(['name' => 'Uncontrolled Sub', 'type' => 'Uncontrolled']);
        $uncontrolledSub->budgets()->create([
            'budget_quantity' => 100.0,
            'status' => 'Approved',
            'created_by' => $user->id,
        ]);

        // 3. Creating direct order for Controlled sub-account going overbudget (150L > 100L remaining)
        // Should require a waiver (is_waiver_pending = true)
        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('chargeable_account_id', $account->id)
            ->set('sub_account_id', $controlledSub->id)
            ->set('say_quantity', 150.0)
            ->set('remarks', 'Controlled test')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('fuel-orders.index'));

        // Assert that the created order has is_waiver_pending = true
        $controlledOrder = FuelOrder::latest('id')->first();
        $this->assertTrue((bool) $controlledOrder->is_waiver_pending);

        // 4. Creating direct order for Uncontrolled sub-account going overbudget (150L > 100L remaining)
        // Should NOT require a waiver (is_waiver_pending = false)
        Livewire::actingAs($user)
            ->test(CreateFuelOrder::class)
            ->set('chargeable_account_id', $account->id)
            ->set('sub_account_id', $uncontrolledSub->id)
            ->set('say_quantity', 150.0)
            ->set('remarks', 'Uncontrolled test')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertRedirect(route('fuel-orders.index'));

        // Assert that the created order has is_waiver_pending = false
        $uncontrolledOrder = FuelOrder::latest('id')->first();
        $this->assertFalse((bool) $uncontrolledOrder->is_waiver_pending);
    }

    public function test_fuel_orders_show_displays_em_dash_for_uncontrolled_sub_accounts_remaining_and_balance()
    {
        $user = User::factory()->create(['role' => 'administrator']);
        $type = AssetType::create(['name' => 'Vehicle']);
        $account = ChargeableAccount::create(['name' => 'General Overhead', 'status' => 'Active']);

        // 1. Create an Uncontrolled sub-account
        $uncontrolledSub = $account->subAccounts()->create(['name' => 'Uncontrolled Sub', 'type' => 'Uncontrolled']);

        $asset = Asset::create([
            'fleet_no' => 'V-101',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'tank_capacity' => 100,
        ]);

        $fuelOrder = FuelOrder::create([
            'asset_id' => $asset->id,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $uncontrolledSub->id,
            'say_quantity' => 100,
            'status' => 'PEND',
            'is_waiver_pending' => false,
            'created_by' => $user->id,
        ]);

        // Add a utilization entry so it has breakdown rows
        UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'reference' => 'REF-001',
            'particulars' => 'Daily Operation',
            'start_kilometer_reading' => 1000,
            'end_kilometer_reading' => 1100, // 100 km diff
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $uncontrolledSub->id,
            'calculation_type' => 'Kilometer Reading',
            'fuel_order_id' => $fuelOrder->id,
        ]);

        $response = $this->actingAs($user)->get(route('fuel-orders.show', $fuelOrder));
        $response->assertStatus(200);

        // Assert that the page contains '—' (em-dash) for Remaining and Balance
        $response->assertSee('—');
    }

    public function test_administrator_and_moderator_can_unlink_sub_account_row()
    {
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $company = Company::factory()->create();
        $admin = User::factory()->create(['company_id' => $company->id, 'role' => 'administrator']);
        $moderator = User::factory()->create(['company_id' => $company->id, 'role' => 'moderator']);
        $standardUser = User::factory()->create(['company_id' => $company->id, 'role' => 'data_logger']);

        $type = AssetType::create(['company_id' => $company->id, 'name' => 'Vehicle']);
        $account = ChargeableAccount::create(['company_id' => $company->id, 'name' => 'General Overhead', 'status' => 'Active']);
        $sub = $account->subAccounts()->create(['company_id' => $company->id, 'name' => 'Sub One']);
        $asset = Asset::create([
            'company_id' => $company->id,
            'fleet_no' => 'V-101',
            'asset_type_id' => $type->id,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'tank_capacity' => 100,
        ]);

        $fuelOrder = FuelOrder::create([
            'company_id' => $company->id,
            'asset_id' => $asset->id,
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'say_quantity' => 100,
            'status' => 'PEND',
            'is_waiver_pending' => false,
            'fuel_factor_km' => 2.5,
            'fuel_factor_hr' => 1.5,
            'created_by' => $admin->id,
        ]);

        // Add 2 utilization entries so we can unlink one and keep one
        $entry1 = UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'reference' => 'REF-001',
            'particulars' => 'Daily Operation',
            'start_kilometer_reading' => 1000,
            'end_kilometer_reading' => 1100, // 100 km diff -> 100 / 2.5 = 40 L
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub->id,
            'calculation_type' => 'Kilometer Reading',
            'fuel_order_id' => $fuelOrder->id,
        ]);

        $sub2 = $account->subAccounts()->create(['name' => 'Sub Two']);
        $entry2 = UtilizationEntry::create([
            'asset_id' => $asset->id,
            'date' => '2026-03-01',
            'start_time' => '08:00',
            'end_time' => '17:00',
            'reference' => 'REF-002',
            'particulars' => 'Secondary Operation',
            'start_kilometer_reading' => 1100,
            'end_kilometer_reading' => 1150, // 50 km diff -> 50 / 2.5 = 20 L
            'driver_operator_name' => 'John Operator',
            'chargeable_account_id' => $account->id,
            'sub_account_id' => $sub2->id,
            'calculation_type' => 'Kilometer Reading',
            'fuel_order_id' => $fuelOrder->id,
        ]);

        // Total calculated_quantity originally is 40 + 20 = 60 L
        $fuelOrder->update([
            'calculated_quantity' => 60,
            'calculated_kilometers' => 150,
        ]);

        // 1. A standard user should be blocked
        $response = $this->actingAs($standardUser)->post(route('fuel-orders.unlink-sub-account', $fuelOrder), [
            'sub_account_id' => $sub->id,
            'unbudgeted' => false,
        ]);
        $response->assertStatus(403);

        // 2. An administrator should be able to unlink Sub One row
        $response = $this->actingAs($admin)->post(route('fuel-orders.unlink-sub-account', $fuelOrder), [
            'sub_account_id' => $sub->id,
            'unbudgeted' => false,
        ]);
        $response->assertRedirect(route('fuel-orders.show', $fuelOrder));
        $response->assertSessionHas('message', 'Sub-account row has been successfully unlinked.');

        // 3. Verify entry1 is unlinked, entry2 is still linked
        $this->assertNull($entry1->fresh()->fuel_order_id);
        $this->assertEquals($fuelOrder->id, $entry2->fresh()->fuel_order_id);

        // 4. Verify fuel order calculations are updated (calculated_quantity should now be 20 L, and km = 50)
        $this->assertEquals(20.0, (float) $fuelOrder->fresh()->calculated_quantity);
        $this->assertEquals(50.0, (float) $fuelOrder->fresh()->calculated_kilometers);

        // 5. A moderator should be able to unlink Sub Two row as well
        $response = $this->actingAs($moderator)->post(route('fuel-orders.unlink-sub-account', $fuelOrder), [
            'sub_account_id' => $sub2->id,
            'unbudgeted' => false,
        ]);
        $response->assertRedirect(route('fuel-orders.show', $fuelOrder));

        // 6. Verify entry2 is also unlinked and order has 0 totals
        $this->assertNull($entry2->fresh()->fuel_order_id);
        $this->assertEquals(0.0, (float) $fuelOrder->fresh()->calculated_quantity);
        $this->assertEquals(0.0, (float) $fuelOrder->fresh()->calculated_kilometers);
    }
}
