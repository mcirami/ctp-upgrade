<?php

require_once __DIR__.'/../vendor/autoload.php';

use App\Offer;
use App\Privilege;
use App\ReportOffer;
use App\Services\DeleteOffer;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Support\Facades\Facade;
use LeadMax\TrackYourStats\Database\DatabaseConnection;
use LeadMax\TrackYourStats\Report\Repositories\Offer\AffiliateOfferRepository;
use LeadMax\TrackYourStats\Report\Repositories\Offer\GodOfferRepository;
use LeadMax\TrackYourStats\Report\Repositories\Offer\AdminOfferRepository;
use LeadMax\TrackYourStats\Report\Repositories\Offer\ManagerOfferRepository;
use PHPUnit\Framework\TestCase;

/** Real MySQL FKs, triggers, view and transactions; never connects to the application DB. */
class OfferDeletionTest extends TestCase
{
    private ?PDO $server = null;
    private Capsule $db;
    private string $database;

    protected function setUp(): void
    {
        if (!getenv('CTP_TEST_MYSQL_PORT')) {
            self::markTestSkipped('Set CTP_TEST_MYSQL_PORT to a disposable local MySQL server (root, empty password).');
        }
        $port = (int) getenv('CTP_TEST_MYSQL_PORT');
        $this->server = new PDO("mysql:host=127.0.0.1;port={$port}", 'root', '', [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        $this->database = 'ctp_offer_test_'.bin2hex(random_bytes(6));
        $this->server->exec("CREATE DATABASE `{$this->database}`");
        $container = new \Illuminate\Foundation\Application(dirname(__DIR__));
        $container->instance('request', \Illuminate\Http\Request::create('/?d_from=2026-11-01'));
        $this->db = new Capsule($container);
        $this->db->setEventDispatcher(new \Illuminate\Events\Dispatcher($container));
        $this->db->addConnection([
            'driver' => 'mysql', 'host' => '127.0.0.1', 'port' => $port,
            'database' => $this->database, 'username' => 'root', 'password' => '',
            'charset' => 'utf8mb4', 'collation' => 'utf8mb4_unicode_ci', 'strict' => false,
        ]);
        $this->db->setAsGlobal();
        $this->db->bootEloquent();
        $container->instance('db', $this->db->getDatabaseManager());
        $container->instance('db.schema', $this->db->schema());
        Facade::setFacadeApplication($container);
        Facade::clearResolvedInstances();
        // Only schema, never data from the installation dump.
        preg_match_all('/CREATE TABLE `[^`]+` \(.*?\) ENGINE=.*?;/s', file_get_contents(__DIR__.'/../base_install.sql'), $tables);
        $connection = $this->db->getConnection();
        $connection->statement('SET FOREIGN_KEY_CHECKS = 0');
        foreach ($tables[0] as $sql) {
            $connection->unprepared($sql);
        }
        $connection->statement('SET FOREIGN_KEY_CHECKS = 1');
        foreach (['2024_07_15_162949_create_user_offer_caps_table', '2026_04_23_113349_create_predefined_geo_rules_tables'] as $name) {
            (require __DIR__.'/../database/migrations/'.$name.'.php')->up();
        }
        $this->migration()->up();
        DatabaseConnection::changeConnection($connection->getPdo());
        $_SESSION = ['userType' => Privilege::ROLE_GOD, 'repid' => 1];
        $_GET = [];
        $this->seed();
    }

    protected function tearDown(): void
    {
        Offer::flushEventListeners();
        if ($this->server && isset($this->database)) {
            $this->db->getDatabaseManager()->disconnect();
            $this->server->exec("DROP DATABASE `{$this->database}`");
        }
        Facade::clearResolvedInstances();
        parent::tearDown();
    }

    private function migration()
    {
        return require __DIR__.'/../database/migrations/2026_11_11_000000_support_permanent_offer_deletion.php';
    }

    private function seed(): void
    {
        $this->db->table('campaigns')->insert(['id' => 1, 'name' => 'Advertiser', 'timestamp' => 0]);
        foreach ([[1,1,8,1], [2,2,7,1], [3,3,6,2], [4,4,5,3]] as [$id,$left,$right,$parent]) {
            $this->db->table('rep')->insert(['idrep'=>$id,'referrer_repid'=>$parent,'lft'=>$left,'rgt'=>$right,'company_name'=>'Test', 'user_name'=>'User '.$id]);
        }
        foreach ([10,20,30] as $id) {
            $this->db->table('offer')->insert(['idoffer'=>$id,'created_by'=>1,'offer_name'=>'Offer '.$id,'url'=>'https://example.test','campaign_id'=>1,'status'=>1]);
            $this->db->table('rep_has_offer')->insert(['rep_idrep'=>4,'offer_idoffer'=>$id,'payout'=>5]);
            $this->db->table('user_offer_caps')->insert(['rep_idrep'=>4,'offer_idoffer'=>$id,'cap'=>10]);
            $this->db->table('bonus_offers')->insert(['offer_id'=>$id,'required_sales'=>1]);
            $this->db->table('offer_caps')->insert(['offer_idoffer'=>$id,'type'=>0,'time_interval'=>0,'interval_cap'=>10,'redirect_offer'=>10]);
            $this->db->table('rule')->insert(['idrule'=>$id,'name'=>'Geo','offer_idoffer'=>$id,'type'=>'geo','redirect_offer'=>10]);
            $this->db->table('geo_rule')->insert(['idgeo_rule'=>$id,'rule_idrule'=>$id]);
            $this->db->table('country_list')->insert(['geo_rule_idgeo_rule'=>$id,'country_code'=>'US']);
            $this->db->table('device_rule')->insert(['iddevice_rule'=>$id,'rule_idrule'=>$id]);
            $this->db->table('device_list')->insert(['device_rule_iddevice_rule'=>$id,'device_type'=>'mobile']);
            $this->db->table('clicks')->insert(['idclicks'=>$id,'rep_idrep'=>4,'offer_idoffer'=>$id,'browser_agent'=>'Test','click_type'=>0,'first_timestamp'=>'2026-11-01 12:00:00']);
            $this->db->table('conversions')->insert(['id'=>$id,'user_id'=>4,'click_id'=>$id,'paid'=>5,'timestamp'=>'2026-11-01 12:01:00']);
        }
        $this->db->table('offer')->where('idoffer',20)->update(['parent'=>10]);
        $this->db->table('predefined_geo_rules')->insert(['rep_idrep'=>1,'name'=>'Saved geo','redirect_offer'=>10]);
        $this->db->table('deductions')->insert(['conversion_id'=>10]);
        $this->db->table('adjustments_log')->insert(['conversion_id'=>10,'user_id'=>1,'action'=>1]);
    }

    public function testPermanentDeletionCleansSettingsAndPreservesHistory(): void
    {
        $history = [];
        foreach (['clicks','conversions','deductions','adjustments_log'] as $table) {
            $history[$table] = $this->db->table($table)->get()->toJson();
        }
        (new DeleteOffer())->delete(10);
        self::assertNull(Offer::find(10));
        self::assertSame('Deleted offer #10', ReportOffer::findOrFail(10)->offer_name);
        foreach (['rep_has_offer','user_offer_caps','offer_caps','rule'] as $table) {
            self::assertSame(0, $this->db->table($table)->where('offer_idoffer',10)->count(), $table);
            self::assertSame(2, $this->db->table($table)->count(), $table);
        }
        foreach (['geo_rule','device_rule','country_list','device_list','bonus_offers'] as $table) {
            self::assertSame(2, $this->db->table($table)->count(), $table);
        }
        foreach ($history as $table => $rows) {
            self::assertSame($rows, $this->db->table($table)->get()->toJson(), $table);
        }
        self::assertNull(Offer::findOrFail(20)->parent);
        self::assertNull($this->db->table('offer_caps')->where('offer_idoffer',20)->value('redirect_offer'));
        self::assertSame(1, $this->db->table('offer_caps')->where('offer_idoffer',20)->value('status'));
        self::assertSame(1, $this->db->table('rule')->where('idrule',20)->value('deny'));
        self::assertSame(0, $this->db->table('rule')->where('idrule',20)->value('redirect_offer'));
        self::assertSame(1, $this->db->table('predefined_geo_rules')->value('deny'));
        (new DeleteOffer())->delete(10);
        self::assertSame(1, $this->db->table('deleted_offers')->count());
    }

    public function testAllOfferReportRolesRetainTotalsAndDeletedDrillDown(): void
    {
        $pdo = $this->db->getConnection()->getPdo();
        $affiliate = new AffiliateOfferRepository($pdo);
        $affiliate->setAffiliateId(4);
        $reports = [[new GodOfferRepository($pdo),1], [new AdminOfferRepository($pdo),2], [new ManagerOfferRepository($pdo),3], [$affiliate,4]];
        $before = [];
        foreach ($reports as $i => [$report,$user]) {
            $_SESSION['repid'] = $user;
            $before[$i] = $report->between('2026-11-01 00:00:00','2026-11-02 00:00:00');
        }
        (new DeleteOffer())->delete(10);
        foreach ($reports as $i => [$report,$user]) {
            $_SESSION['repid'] = $user;
            $after = $report->between('2026-11-01 00:00:00','2026-11-02 00:00:00');
            self::assertCount(3, $after);
            foreach (['Clicks','UniqueClicks','Conversions','Revenue','Deductions'] as $metric) {
                self::assertEquals(array_sum(array_column($before[$i],$metric)), array_sum(array_column($after,$metric)), get_class($report).' '.$metric);
            }
            $deleted = array_values(array_filter($after, fn ($row) => $row['idoffer'] == 10))[0];
            self::assertSame('Deleted offer #10', $deleted['offer_name']);
            self::assertEquals(1, $deleted['is_deleted']);
        }
        $bound = (new ReportOffer())->resolveRouteBinding(10);
        self::assertSame('Deleted offer #10', $bound->offer_name);
        self::assertNull((new Offer())->resolveRouteBinding(10));
        $formatter = new \LeadMax\TrackYourStats\Report\Formats\HTML(false, ['idoffer','offer_name']);
        ob_start();
        $formatter->output([$deleted]);
        $html = ob_get_clean();
        self::assertStringContainsString('Deleted offer #10', $html);
        self::assertStringNotContainsString('offer_update.php', $html);
    }

    public function testAdvertiserTotalsAndDetailLabelsSurviveDeletion(): void
    {
        $report = new \LeadMax\TrackYourStats\Report\Repositories\AdvertiserRepository($this->db->getConnection()->getPdo());
        $before = $report->between('2026-11-01 00:00:00','2026-11-02 00:00:00');
        (new DeleteOffer())->delete(10);
        self::assertSame($before, $report->between('2026-11-01 00:00:00','2026-11-02 00:00:00'));
        self::assertSame('Deleted offer #10', ReportOffer::where('campaign_id', 1)->findOrFail(10)->offer_name);
    }

    public function testFailureRollsBackEveryChange(): void
    {
        Offer::deleting(function () { throw new RuntimeException('Simulated failure'); });
        try {
            (new DeleteOffer())->delete(10);
            self::fail('Deletion should fail');
        } catch (RuntimeException $e) {
            self::assertSame('Simulated failure', $e->getMessage());
        }
        self::assertNotNull(Offer::find(10));
        self::assertSame(0, $this->db->table('deleted_offers')->count());
        foreach (['rep_has_offer','rule','geo_rule','device_rule','country_list','device_list','offer_caps','user_offer_caps','bonus_offers'] as $table) {
            self::assertSame(3, $this->db->table($table)->count(), $table);
        }
        self::assertEquals(10, Offer::findOrFail(20)->parent);
        self::assertEquals(10, $this->db->table('offer_caps')->where('offer_idoffer',20)->value('redirect_offer'));
    }

    public function testDeletedIdsCannotBeReusedAndRollbackCannotEraseHistory(): void
    {
        (new DeleteOffer())->delete(10);
        foreach (['insert','update'] as $action) {
            try {
                if ($action === 'insert') {
                    $this->db->table('offer')->insert(['idoffer'=>10,'created_by'=>1,'url'=>'https://example.test','campaign_id'=>1]);
                } else {
                    $this->db->table('offer')->where('idoffer',20)->update(['idoffer'=>10]);
                }
                self::fail('A deleted ID was reused');
            } catch (\Illuminate\Database\QueryException $e) {
                self::assertStringContainsString('deleted offer ID cannot be reused', $e->getMessage());
            }
        }
        try {
            $this->migration()->down();
            self::fail('Rollback erased history');
        } catch (RuntimeException $e) {
            self::assertStringContainsString('Cannot roll back', $e->getMessage());
        }
        self::assertSame('Deleted offer #10', ReportOffer::findOrFail(10)->offer_name);
    }

    public function testMigrationCanRollBackBeforeAnyDeletion(): void
    {
        $this->migration()->down();
        self::assertFalse($this->db->schema()->hasTable('deleted_offers'));
        $this->migration()->up();
        self::assertCount(3, ReportOffer::all());
    }

    public function testDeletionRouteAndControllerRequireGodAndUnsafeMethod(): void
    {
        $container = Facade::getFacadeApplication();
        $router = new \Illuminate\Routing\Router(new \Illuminate\Events\Dispatcher($container), $container);
        $container->instance('router', $router);
        if (!class_exists('Route')) {
            class_alias(\Illuminate\Support\Facades\Route::class, 'Route');
        }
        require __DIR__.'/../routes/web.php';
        $route = $router->getRoutes()->match(\Illuminate\Http\Request::create('/offer/10', 'DELETE'));
        self::assertContains('legacy.auth', $route->middleware());
        self::assertContains('role:0', $route->middleware());
        foreach ($router->getRoutes() as $candidate) {
            if ($candidate->getActionName() === App\Http\Controllers\OfferController::class.'@delete') {
                self::assertSame(['DELETE'], $candidate->methods());
            }
        }
        foreach ([Privilege::ROLE_ADMIN,Privilege::ROLE_MANAGER,Privilege::ROLE_AFFILIATE] as $role) {
            $_SESSION['userType'] = $role;
            try {
                (new App\Http\Controllers\OfferController())->delete(10,new DeleteOffer());
                self::fail('Unauthorized deletion');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                self::assertSame(403,$e->getStatusCode());
            }
            self::assertNotNull(Offer::find(10));
        }
    }

    public function testDeleteRequiresCsrfToken(): void
    {
        $container = Facade::getFacadeApplication();
        $container->instance('env', 'local');
        $session = new \Illuminate\Session\Store('test', new \Illuminate\Session\ArraySessionHandler(120));
        $session->put('_token', 'test-token');
        $request = \Illuminate\Http\Request::create('/offer/10', 'DELETE');
        $request->setLaravelSession($session);
        $middleware = new class($container, new \Illuminate\Encryption\Encrypter(str_repeat('a',32), 'AES-256-CBC')) extends App\Http\Middleware\VerifyCsrfToken {
            protected $addHttpCookie = false;
        };
        try {
            $middleware->handle($request, fn () => new \Illuminate\Http\Response('accepted'));
            self::fail('Missing CSRF token accepted');
        } catch (\Illuminate\Session\TokenMismatchException $e) {
            self::assertNotNull(Offer::find(10));
        }
        $request->request->set('_token', 'test-token');
        self::assertSame('accepted', $middleware->handle($request, fn () => new \Illuminate\Http\Response('accepted'))->getContent());
    }

    public function testLateConversionAndMissingCapRedirectAreHandled(): void
    {
        (new DeleteOffer())->delete(10);
        $event = new \LeadMax\TrackYourStats\Clicks\URLEvents\ConversionRegistrationEvent(10);
        self::assertSame(410, $event->fire()->getStatusCode());
        self::assertSame(3, $this->db->table('conversions')->count());
        $caps = new \LeadMax\TrackYourStats\Offer\Caps(20);
        try {
            $caps->sendToRedirectOffer();
            self::fail('Missing fallback should block traffic');
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            self::assertSame(410, $e->getStatusCode());
        }
    }
}
