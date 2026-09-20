<?php

namespace Tests\Feature;

use App\Enums\TransactionType;
use App\Models\Drink;
use App\Models\Drinker;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MeteImportTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['database.connections.mete' => [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => false,
        ]]);

        $mete = DB::connection('mete');

        $mete->statement('create table users (id integer primary key, name text, balance numeric, active boolean default 1, created_at datetime, updated_at datetime)');
        $mete->statement('create table drinks (id integer primary key, name text, price numeric, bottle_size numeric, active boolean default 1, created_at datetime, updated_at datetime)');
        $mete->statement('create table audits (id integer primary key autoincrement, created_at datetime, difference numeric, drink integer, "user" integer)');

        $mete->table('drinks')->insert([
            'id' => 1, 'name' => 'Mate', 'price' => 1.5, 'bottle_size' => 0.5, 'active' => 1,
            'created_at' => '2023-12-01 00:00:00', 'updated_at' => '2023-12-01 00:00:00',
        ]);

        $mete->table('users')->insert([
            'id' => 1, 'name' => 'Alice', 'balance' => 10.00, 'active' => 1,
            'created_at' => '2023-12-01 00:00:00', 'updated_at' => '2023-12-01 00:00:00',
        ]);

        $mete->table('audits')->insert([
            ['created_at' => '2024-01-01 10:00:00', 'difference' => 20.00, 'drink' => null, 'user' => 1],
            ['created_at' => '2024-01-01 11:00:00', 'difference' => -1.50, 'drink' => 1, 'user' => 1],
            ['created_at' => '2024-01-02 09:00:00', 'difference' => -8.50, 'drink' => null, 'user' => 1],
        ]);
    }

    public function test_dry_run_reports_counts_without_writing(): void
    {
        $this->artisan('mete:import', ['--dry-run' => true])->assertExitCode(0);

        $this->assertSame(0, Drink::count());
        $this->assertSame(0, Drinker::count());
        $this->assertSame(0, Transaction::count());
    }

    public function test_it_imports_drinks_drinkers_and_backfills_transaction_balances(): void
    {
        $this->artisan('mete:import')->assertExitCode(0);

        $drink = Drink::where('legacy_id', 1)->firstOrFail();
        $this->assertSame('Mate', $drink->name);

        $drinker = Drinker::where('legacy_id', 1)->firstOrFail();
        $this->assertSame('Alice', $drinker->name);
        $this->assertSame('10.00', $drinker->balance);

        $this->assertSame(3, $drinker->transactions()->count());

        $deposit = Transaction::where('drinker_id', $drinker->id)->where('amount', 20)->firstOrFail();
        $this->assertSame(TransactionType::Deposit, $deposit->type);
        $this->assertSame('20.00', $deposit->balance_after);

        $purchase = Transaction::where('drinker_id', $drinker->id)->where('amount', -1.5)->firstOrFail();
        $this->assertSame(TransactionType::Purchase, $purchase->type);
        $this->assertSame($drink->id, $purchase->drink_id);
        $this->assertSame('18.50', $purchase->balance_after);

        $adjustment = Transaction::where('drinker_id', $drinker->id)->where('amount', -8.5)->firstOrFail();
        $this->assertSame(TransactionType::Adjustment, $adjustment->type);
        $this->assertSame('10.00', $adjustment->balance_after);
    }

    public function test_it_is_idempotent_when_run_twice(): void
    {
        $this->artisan('mete:import')->assertExitCode(0);
        $this->artisan('mete:import')->assertExitCode(0);

        $this->assertSame(1, Drink::count());
        $this->assertSame(1, Drinker::count());
        $this->assertSame(3, Transaction::count());
    }
}
