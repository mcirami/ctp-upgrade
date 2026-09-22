<?php

namespace Tests;

use App\Http\Controllers\AdjustmentsController;
use Illuminate\Database\Capsule\Manager;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use PHPUnit\Framework\TestCase;

class ManualSaleTest extends TestCase
{
    private Manager $database;

    protected function setUp(): void
    {
        parent::setUp();
        $this->database = new Manager();
        $this->database->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $this->database->setAsGlobal();
        $this->database->bootEloquent();
        $connection = $this->database->getConnection();
        $connection->statement('CREATE TABLE rep (idrep INTEGER PRIMARY KEY, user_name TEXT, status INTEGER, lft INTEGER, rgt INTEGER)');
        $connection->statement('CREATE TABLE privileges (idprivileges INTEGER PRIMARY KEY, rep_idrep INTEGER, is_rep INTEGER DEFAULT 0, is_god INTEGER DEFAULT 0, is_admin INTEGER DEFAULT 0, is_manager INTEGER DEFAULT 0)');
        $connection->statement('CREATE TABLE offer (idoffer INTEGER PRIMARY KEY, offer_name TEXT, status INTEGER)');
        $connection->statement('CREATE TABLE rep_has_offer (rep_idrep INTEGER, offer_idoffer INTEGER, payout REAL)');
        $connection->table('rep')->insert([
            ['idrep' => 1, 'user_name' => 'Manager', 'status' => 1, 'lft' => 1, 'rgt' => 10],
            ['idrep' => 2, 'user_name' => 'Affiliate', 'status' => 1, 'lft' => 2, 'rgt' => 3],
            ['idrep' => 3, 'user_name' => 'Inactive', 'status' => 0, 'lft' => 4, 'rgt' => 5],
            ['idrep' => 4, 'user_name' => 'Outside', 'status' => 1, 'lft' => 11, 'rgt' => 12],
            ['idrep' => 5, 'user_name' => 'Submanager', 'status' => 1, 'lft' => 6, 'rgt' => 9],
        ]);
        foreach ([2, 3, 4] as $id) {
            $connection->table('privileges')->insert(['rep_idrep' => $id, 'is_rep' => 1]);
        }
        $connection->table('privileges')->insert(['rep_idrep' => 5, 'is_manager' => 1]);
        $connection->table('offer')->insert([
            ['idoffer' => 10, 'offer_name' => 'Assigned', 'status' => 1],
            ['idoffer' => 11, 'offer_name' => 'Inactive', 'status' => 0],
            ['idoffer' => 12, 'offer_name' => 'Unassigned', 'status' => 1],
        ]);
        $connection->table('rep_has_offer')->insert([
            ['rep_idrep' => 2, 'offer_idoffer' => 10, 'payout' => 1.50],
            ['rep_idrep' => 2, 'offer_idoffer' => 11, 'payout' => 2.00],
        ]);
        $_SESSION['repid'] = 1;
    }

    protected function tearDown(): void
    {
        unset($_SESSION['repid']);
        $this->database->getConnection()->disconnect();
        parent::tearDown();
    }

    public function test_lists_only_active_managed_affiliates(): void
    {
        $this->assertSame([['id' => 2, 'name' => 'Affiliate']], (new AdjustmentsController())->getAffiliates()->toArray());
    }

    public function test_lists_only_active_assigned_offers(): void
    {
        $offers = (new AdjustmentsController())->getAffiliatesOffers(2);
        $this->assertSame([10], $offers->pluck('id')->all());
        $this->assertSame('Assigned', $offers->first()->name);
    }

    public function test_rejects_affiliates_outside_manager_scope(): void
    {
        $this->expectException(ModelNotFoundException::class);
        (new AdjustmentsController())->getAffiliatesOffers(4);
    }

    public function test_rejects_inactive_affiliates(): void
    {
        $this->expectException(ModelNotFoundException::class);
        (new AdjustmentsController())->getAffiliatesOffers(3);
    }

    public function test_rejects_non_affiliates(): void
    {
        $this->expectException(ModelNotFoundException::class);
        (new AdjustmentsController())->getAffiliatesOffers(5);
    }
}
