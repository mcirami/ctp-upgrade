<?php

require_once __DIR__.'/../vendor/autoload.php';

use App\User;
use App\Privilege;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Facade;
use LeadMax\TrackYourStats\Database\DatabaseConnection;
use LeadMax\TrackYourStats\User\Permissions;
use PHPUnit\Framework\TestCase;

class LanderAccessTest extends TestCase
{
    private Capsule $db;

    protected function setUp(): void
    {
        $container = new Container();
        $this->db = new Capsule($container);
        $this->db->addConnection(['driver' => 'sqlite', 'database' => ':memory:']);
        $this->db->setAsGlobal();
        $this->db->bootEloquent();
        $container->instance('db', $this->db->getDatabaseManager());
        Facade::setFacadeApplication($container);
        Facade::clearResolvedInstances();
        $schema = $this->db->schema();
        $container->instance('db.schema', $schema);
        $schema->create('rep', function (Blueprint $table) {
            $table->integer('idrep')->primary();
            $table->integer('lft');
            $table->integer('rgt');
        });
        $schema->create('privileges', function (Blueprint $table) {
            $table->integer('rep_idrep');
            foreach (['god', 'admin', 'manager', 'rep'] as $role) {
                $table->boolean('is_'.$role)->default(false);
            }
        });
        $schema->create('permissions', function (Blueprint $table) {
            $table->integer('aff_id')->primary();
            foreach (Permissions::$permissionsArray as $name => $details) {
                if ($name !== 'aff_id' && $name !== Permissions::LANDER_ACCESS) {
                    $table->boolean($name)->default(false);
                }
            }
        });
        $migration = require __DIR__.'/../database/migrations/2026_10_30_000000_add_lander_urls_and_access.php';
        $migration->up();
        $override = require __DIR__.'/../database/migrations/2026_10_30_000001_add_agent_lander_access_override.php';
        $override->up();
        // Two admins; the first has two offices, each with an agent.
        foreach ([[1,1,20,'god'], [2,2,13,'admin'], [3,3,8,'manager'],
                  [4,4,5,'rep'], [5,9,12,'manager'], [6,10,11,'rep'],
                  [7,14,19,'admin'], [8,15,18,'manager'], [9,16,17,'rep']] as [$id,$left,$right,$role]) {
            $this->db->table('rep')->insert(['idrep'=>$id,'lft'=>$left,'rgt'=>$right]);
            $this->db->table('privileges')->insert(['rep_idrep'=>$id,'is_'.$role=>1]);
            $this->db->table('permissions')->insert(['aff_id'=>$id]);
        }
        DatabaseConnection::changeConnection($this->db->getConnection()->getPdo());
        $_SESSION = ['userType' => Privilege::ROLE_GOD];
        $_GET = [];
    }

    private function grant(int $id, bool $value = true): void
    {
        $this->db->table('permissions')->where('aff_id', $id)->update(['lander_access'=>$value]);
    }

    public function testInheritanceIsolationAndImmediateRevocation(): void
    {
        $agent = User::find(4);
        self::assertFalse($agent->hasLanderAccess());
        $this->grant(1); // God's own permissions must not grant the entire tree access.
        self::assertFalse($agent->hasLanderAccess());
        $this->grant(2);
        self::assertTrue($agent->hasLanderAccess());
        self::assertTrue(User::find(6)->hasLanderAccess());
        self::assertFalse(User::find(9)->hasLanderAccess());
        $this->grant(2, false);
        self::assertFalse($agent->hasLanderAccess());
        $this->grant(3);
        self::assertTrue($agent->hasLanderAccess());
        self::assertFalse(User::find(6)->hasLanderAccess());
        $this->grant(4);
        $this->grant(3, false);
        self::assertTrue($agent->hasLanderAccess());
        $this->grant(4, false);
        self::assertFalse($agent->hasLanderAccess());
    }

    public function testOnlyGodCanWriteGrantsAndOtherEditorsPreserveThem(): void
    {
        // Avoid the legacy constructor's MySQL DESCRIBE; exercise the actual write methods.
        $permissions = (new ReflectionClass(Permissions::class))->newInstanceWithoutConstructor();
        foreach ([Privilege::ROLE_ADMIN, Privilege::ROLE_MANAGER, Privilege::ROLE_AFFILIATE] as $role) {
            $_SESSION['userType'] = $role;
            $permissions->updatePermissions(['lander_access'=>1], 4);
            self::assertFalse(User::find(4)->hasLanderAccess());
            $this->grant(4);
            $permissions->updatePermissions([], 4);
            self::assertTrue(User::find(4)->hasLanderAccess());
            $this->grant(4, false);
            $id = 100 + $role;
            $permissions->createPermissions(['aff_id'=>$id, 'lander_access'=>1]);
            self::assertEquals(0, $this->db->table('permissions')->where('aff_id',$id)->value('lander_access'));
        }
        $_SESSION['userType'] = Privilege::ROLE_GOD;
        $permissions->updatePermissions(['lander_access'=>1], 4);
        self::assertTrue(User::find(4)->hasLanderAccess());
        $permissions->updatePermissions([], 4);
        self::assertFalse(User::find(4)->hasLanderAccess());
    }

    public function testInheritedCheckboxAndAgentException(): void
    {
        $permissions = (new ReflectionClass(Permissions::class))->newInstanceWithoutConstructor();
        $permissions->repID = 4;
        $print = new ReflectionMethod(Permissions::class, 'printPermission');
        $checkbox = function () use ($permissions, $print) {
            ob_start();
            $print->invoke($permissions, Permissions::LANDER_ACCESS, true, Privilege::ROLE_AFFILIATE);
            return ob_get_clean();
        };
        $this->grant(2);
        self::assertStringContainsString("'checked':'checked'", $checkbox());
        // Saving another field keeps inherited access inherited.
        $permissions->updatePermissions(['lander_access'=>1], 4);
        self::assertEquals(0, $this->db->table('permissions')->where('aff_id',4)->value('lander_access'));
        $this->grant(2, false);
        self::assertFalse(User::find(4)->hasLanderAccess());
        $this->grant(2);
        $permissions->updatePermissions([], 4);
        self::assertFalse(User::find(4)->hasLanderAccess());
        self::assertTrue(User::find(6)->hasLanderAccess());
        self::assertStringNotContainsString("'checked':'checked'", $checkbox());
        $this->grant(3); // An office grant must also respect the agent exception.
        self::assertFalse(User::find(4)->hasLanderAccess());
        foreach ([Privilege::ROLE_ADMIN, Privilege::ROLE_MANAGER, Privilege::ROLE_AFFILIATE] as $role) {
            $_SESSION['userType'] = $role;
            $permissions->updatePermissions(['lander_access'=>1], 4);
            self::assertFalse(User::find(4)->hasLanderAccess());
            self::assertFalse($permissions->updatePermissions(['lander_access_denied'=>0], 4));
            self::assertFalse(User::find(4)->hasLanderAccess());
        }
        $_SESSION['userType'] = Privilege::ROLE_GOD;
        $permissions->updatePermissions(['lander_access'=>1], 4);
        self::assertTrue(User::find(4)->hasLanderAccess());
        self::assertStringContainsString("'checked':'checked'", $checkbox());
        $this->grant(2, false);
        $this->grant(3, false);
        self::assertFalse(User::find(4)->hasLanderAccess());
    }

    public function testManagementRoutesRequireGod(): void
    {
        $container = Facade::getFacadeApplication();
        $router = new \Illuminate\Routing\Router(new \Illuminate\Events\Dispatcher($container), $container);
        $container->instance('router', $router);
        if (!class_exists('Route')) {
            class_alias(\Illuminate\Support\Facades\Route::class, 'Route');
        }
        require __DIR__.'/../routes/web.php';
        $count = 0;
        foreach ($router->getRoutes() as $route) {
            if (str_starts_with($route->uri(), 'lander/urls')) {
                self::assertContains('legacy.auth', $route->middleware());
                self::assertContains('role:0', $route->middleware());
                $count++;
            }
        }
        self::assertSame(5, $count);
    }

    public function testUrlValidationAcceptsPathsAndRejectsUnsafeSchemes(): void
    {
        $translator = new \Illuminate\Translation\Translator(new \Illuminate\Translation\ArrayLoader(), 'en');
        $factory = new \Illuminate\Validation\Factory($translator);
        \Illuminate\Http\Request::macro('validate', function ($rules) use ($factory) {
            return $factory->make($this->all(), $rules)->validate();
        });
        $method = new ReflectionMethod(App\Http\Controllers\LanderUrlController::class, 'validated');
        $controller = new App\Http\Controllers\LanderUrlController();
        foreach (['https://example.com/g4/', 'https://example.com/path?source=x#section'] as $url) {
            $request = \Illuminate\Http\Request::create('/', 'POST', ['url'=>$url, 'status'=>'1']);
            self::assertSame($url, $method->invoke($controller, $request)['url']);
        }
        foreach (['javascript:alert(1)', 'ftp://example.com/path', 'not a url'] as $url) {
            try {
                $method->invoke($controller, \Illuminate\Http\Request::create('/', 'POST', ['url'=>$url, 'status'=>'1']));
                self::fail('Unsafe URL accepted');
            } catch (\Illuminate\Validation\ValidationException $exception) {
                self::assertArrayHasKey('url', $exception->errors());
            }
        }
    }

    public function testLanderStorageAndMigrationRollback(): void
    {
        App\LanderUrl::create(['url'=>'https://example.com/g4/', 'status'=>1]);
        App\LanderUrl::create(['url'=>'https://example.com/hidden/', 'status'=>0]);
        self::assertSame(['https://example.com/g4/'], App\LanderUrl::where('status',1)->pluck('url')->all());
        $migration = require __DIR__.'/../database/migrations/2026_10_30_000000_add_lander_urls_and_access.php';
        $override = require __DIR__.'/../database/migrations/2026_10_30_000001_add_agent_lander_access_override.php';
        $override->down();
        self::assertFalse($this->db->schema()->hasColumn('permissions', 'lander_access_denied'));
        $migration->down();
        self::assertFalse($this->db->schema()->hasTable('lander_urls'));
        self::assertFalse($this->db->schema()->hasColumn('permissions','lander_access'));
    }
}
