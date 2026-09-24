<?php
/** Announcement regression tests use isolated SQLite and temporary storage only. */
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }
ini_set('zend.exception_ignore_args', '1');
require __DIR__.'/../vendor/autoload.php';

use App\Announcement;
use App\Http\Controllers\AnnouncementController;
use App\Privilege;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use LeadMax\TrackYourStats\User\Permissions;

$app = require __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
config(['session.driver'=>'array','database.default'=>'sqlite','database.connections.sqlite'=>['driver'=>'sqlite','database'=>':memory:','prefix'=>'']]);
DB::purge('sqlite');
$schema = DB::connection()->getSchemaBuilder();
$schema->create('permissions', function($t){$t->integer('aff_id')->primary();$t->tinyInteger('create_affiliates')->default(0);});
$schema->create('privileges', function($t){$t->increments('idprivileges');$t->integer('rep_idrep');$t->tinyInteger('is_god')->default(0);$t->tinyInteger('is_admin')->default(0);$t->tinyInteger('is_manager')->default(0);$t->tinyInteger('is_rep')->default(0);});
DB::table('permissions')->insert([['aff_id'=>1],['aff_id'=>2]]);
DB::table('privileges')->insert([['rep_idrep'=>1,'is_god'=>1,'is_admin'=>0,'is_manager'=>0,'is_rep'=>0],['rep_idrep'=>2,'is_god'=>0,'is_admin'=>1,'is_manager'=>0,'is_rep'=>0]]);
require __DIR__.'/../database/migrations/2026_09_24_000001_create_announcements_table_and_permission.php';
$migration = new CreateAnnouncementsTableAndPermission(); $migration->up();
$checks=0;
function verifyAnnouncement($ok,$message){global $checks;if(!$ok)throw new RuntimeException($message);$checks++;}
verifyAnnouncement($schema->hasTable('announcements'),'Announcements migration missing table');
verifyAnnouncement($schema->hasColumn('permissions',Permissions::CREATE_ANNOUNCEMENTS),'Permission column missing');
verifyAnnouncement((int)DB::table('permissions')->where('aff_id',1)->value(Permissions::CREATE_ANNOUNCEMENTS)===1,'Network Admin permission not enabled');
verifyAnnouncement((int)DB::table('permissions')->where('aff_id',2)->value(Permissions::CREATE_ANNOUNCEMENTS)===0,'Regular Admin permission enabled by default');
verifyAnnouncement(Permissions::$permissionsArray[Permissions::CREATE_ANNOUNCEMENTS]['allowed_user_types']===[Privilege::ROLE_GOD,Privilege::ROLE_ADMIN],'Permission role metadata incorrect');

$temp=sys_get_temp_dir().'/announcement-test-'.bin2hex(random_bytes(6));mkdir($temp,0755,true);
config(['filesystems.disks.local'=>['driver'=>'local','root'=>$temp]]); Storage::forgetDisk('local');
$session=app('session')->driver();$session->start();app('redirect')->setSession($session);
$_SESSION=['repid'=>1];
file_put_contents($temp.'/source.pdf','safe fixture');
$request=Request::create('/announcements','POST',['title'=>'Launch','type'=>'info','body'=>'Details','is_pinned'=>'1'],[],['attachment'=>new UploadedFile($temp.'/source.pdf','Launch Notes.pdf','application/pdf',null,true)]);
$request->setLaravelSession($session);$app->instance('request',$request);
$controller=new AnnouncementController();$response=$controller->store($request);
verifyAnnouncement($response->getStatusCode()===302 && str_ends_with($response->getTargetUrl(),'/announcements'),'Save did not redirect to announcement management');
$announcement=Announcement::query()->first();
verifyAnnouncement($announcement && $announcement->title==='Launch' && $announcement->is_pinned,'Announcement fields not saved');
verifyAnnouncement($announcement->attachment_name==='Launch Notes.pdf' && Storage::disk('local')->exists($announcement->attachment_path),'Private attachment not saved');
verifyAnnouncement(!str_contains($announcement->attachment_path,'Launch Notes'),'Original filename exposed in storage path');
$originalPath=$announcement->attachment_path;
$download=$controller->download($announcement);
verifyAnnouncement($download->getStatusCode()===200 && str_contains((string)$download->headers->get('content-disposition'),'Launch Notes.pdf'),'Protected download response incorrect');
$editRequest=Request::create('/announcements/'.$announcement->id,'PUT',['title'=>'Updated Launch','type'=>'bonus','body'=>'Revised details']);
$editRequest->setLaravelSession($session);$app->instance('request',$editRequest);$editResponse=$controller->update($editRequest,$announcement);
$announcement->refresh();
verifyAnnouncement($editResponse->isRedirect()&&$announcement->title==='Updated Launch'&&$announcement->type==='bonus','Announcement text update failed');
verifyAnnouncement($announcement->attachment_path===$originalPath&&Storage::disk('local')->exists($originalPath),'Normal edit removed the existing attachment');
file_put_contents($temp.'/replacement.txt','replacement fixture');
$replaceRequest=Request::create('/announcements/'.$announcement->id,'PUT',['title'=>'Updated Launch','type'=>'info','body'=>'With replacement'],[],['attachment'=>new UploadedFile($temp.'/replacement.txt','Replacement.txt','text/plain',null,true)]);
$replaceRequest->setLaravelSession($session);$app->instance('request',$replaceRequest);$controller->update($replaceRequest,$announcement);$announcement->refresh();
verifyAnnouncement($announcement->attachment_name==='Replacement.txt'&&Storage::disk('local')->exists($announcement->attachment_path),'Replacement attachment was not saved');
verifyAnnouncement(!Storage::disk('local')->exists($originalPath),'Replaced attachment was not cleaned up');
$replacementPath=$announcement->attachment_path;
$invalid=Request::create('/announcements','POST',['title'=>'','type'=>'script','body'=>'']);
try{$controller->store($invalid);verifyAnnouncement(false,'Invalid announcement accepted');}catch(Illuminate\Validation\ValidationException $e){verifyAnnouncement(true,'Invalid announcement blocked');}
verifyAnnouncement(Announcement::query()->count()===1,'Invalid request created a row');
foreach ([['GET','announcements'],['GET','announcements/create'],['POST','announcements'],['GET','announcements/1/edit'],['PUT','announcements/1'],['DELETE','announcements/1']] as [$method,$path]) {$route=app('router')->getRoutes()->match(Request::create('/'.$path,$method));$mw=$route->gatherMiddleware();verifyAnnouncement(in_array('legacy.auth',$mw)&&in_array('role:0,1',$mw)&&in_array('permissions:'.Permissions::CREATE_ANNOUNCEMENTS,$mw),'Announcement management route missing access controls');}
$route=app('router')->getRoutes()->match(Request::create('/announcements/1/attachment','GET'));verifyAnnouncement(in_array('legacy.auth',$route->gatherMiddleware()),'Download route is public');
class AnnouncementPermissionStub { public function __construct(private bool $allowed) {} public function can($permission) { return $this->allowed && $permission === Permissions::CREATE_ANNOUNCEMENTS; } }
$_SESSION['userType']=Privilege::ROLE_ADMIN; $_SESSION['permissions']=serialize(new AnnouncementPermissionStub(false));
$denied=(new App\Http\Middleware\LegacyPermissionMiddleware())->handle($request,fn()=>response('allowed'),Permissions::CREATE_ANNOUNCEMENTS);
verifyAnnouncement($denied->isRedirect() && str_ends_with($denied->getTargetUrl(),'/dashboard'),'Admin without permission was allowed');
$_SESSION['permissions']=serialize(new AnnouncementPermissionStub(true));
$allowed=(new App\Http\Middleware\LegacyPermissionMiddleware())->handle($request,fn()=>response('allowed'),Permissions::CREATE_ANNOUNCEMENTS);
verifyAnnouncement($allowed->getContent()==='allowed','Permitted Admin was denied');
$_SESSION['userType']=Privilege::ROLE_GOD;
$_SESSION['permissions']=serialize(new AnnouncementPermissionStub(false));
$godAllowed=(new App\Http\Middleware\LegacyPermissionMiddleware())->handle($request,fn()=>response('allowed'),Permissions::CREATE_ANNOUNCEMENTS);
verifyAnnouncement($godAllowed->getContent()==='allowed','God without cached announcement permission was denied');
$otherDenied=(new App\Http\Middleware\LegacyPermissionMiddleware())->handle($request,fn()=>response('allowed'),Permissions::CREATE_OFFERS);
verifyAnnouncement($otherDenied->isRedirect(),'Announcement change bypassed unrelated permissions');
$_SERVER['REQUEST_URI']='/announcements';
foreach ([[Privilege::ROLE_GOD,false,true],[Privilege::ROLE_ADMIN,true,true],[Privilege::ROLE_ADMIN,false,false],[Privilege::ROLE_MANAGER,true,false],[Privilege::ROLE_AFFILIATE,true,false]] as [$role,$permission,$visible]) {
    $nav=new LeadMax\TrackYourStats\System\NavBar($role,new AnnouncementPermissionStub($permission));
    foreach ([false,true] as $mobile) {
        ob_start();$nav->printNav($mobile);$html=ob_get_clean();
        verifyAnnouncement(str_contains($html,'href="/announcements"')===$visible,'Announcement menu visibility incorrect');
        verifyAnnouncement(!str_contains($html,'/notifications.php') && !str_contains($html,'>Manage Announcements<') && !str_contains($html,'>Create Announcement<'),'Old or duplicate Account menu entry remains');
    }
}
// User forms must offer the new permission even with an older target record/session.
$permissionForm=(new ReflectionClass(Permissions::class))->newInstanceWithoutConstructor();
$permissionForm->permissions=[Permissions::CREATE_NOTIFICATIONS=>1];
$canPrint=new ReflectionMethod(Permissions::class,'canPrintPermission');
foreach ([[Privilege::ROLE_GOD,false,true],[Privilege::ROLE_ADMIN,true,true],[Privilege::ROLE_ADMIN,false,false],[Privilege::ROLE_MANAGER,true,false]] as [$editorRole,$granted,$expected]) {
    $_SESSION['userType']=$editorRole;$_SESSION['permissions']=serialize(new AnnouncementPermissionStub($granted));
    verifyAnnouncement($canPrint->invoke($permissionForm,Permissions::CREATE_ANNOUNCEMENTS,Privilege::ROLE_ADMIN)===$expected,'Announcement permission form availability incorrect');
    verifyAnnouncement(!$canPrint->invoke($permissionForm,Permissions::CREATE_NOTIFICATIONS,Privilege::ROLE_ADMIN),'Retired Notifications option still visible');
    foreach ([Privilege::ROLE_MANAGER,Privilege::ROLE_AFFILIATE] as $targetRole) {
        verifyAnnouncement(!$canPrint->invoke($permissionForm,Permissions::CREATE_ANNOUNCEMENTS,$targetRole),'Announcement permission offered for non-admin target');
    }
}
$_SESSION['userType']=Privilege::ROLE_GOD;$_SESSION['permissions']=serialize(new AnnouncementPermissionStub(false));
foreach ([false,true] as $editing) {
    ob_start();$permissionForm->dumpPermissionsToJavascript($editing);$permissionHtml=ob_get_clean();
    verifyAnnouncement(str_contains($permissionHtml,'Can Create Announcements') && !str_contains($permissionHtml,'Can Create Notifications'),'Create/edit permission labels incorrect');
}
// Render the actual account body without its database-dependent master layout.
$accountTemplate=str_replace(["@extends('layouts.master')","@section('content')","@endsection"],'',file_get_contents(__DIR__.'/../resources/views/home.blade.php'));
foreach ([0,1,2,3,'3',false] as $role) {
    $accountHtml=Illuminate\Support\Facades\Blade::render($accountTemplate,[
        'userType'=>$role,'announcements'=>collect([$announcement]),'firstName'=>'Test',
        'email'=>'test@example.com','webroot'=>'/','userId'=>1,'canViewPostback'=>false,'domain'=>'/signup.php?mid=',
    ]);
    verifyAnnouncement(str_contains($accountHtml,'id="announcements-heading"')===((string)$role==='3'),'Account feed visible to incorrect role');
}
$_SESSION['userType']=Privilege::ROLE_MANAGER;
try{(new App\Http\Middleware\LegacyAccountTypeMiddleware())->handle($request,fn()=>response('allowed'),'0','1');verifyAnnouncement(false,'Manager reached Admin create route');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){verifyAnnouncement($e->getStatusCode()===403,'Manager role denial was incorrect');}
// Affiliates cannot manage announcements even if a permission is accidentally assigned.
$_SESSION['userType']=Privilege::ROLE_AFFILIATE;
try{(new App\Http\Middleware\LegacyAccountTypeMiddleware())->handle($request,fn()=>response('allowed'),'0','1');verifyAnnouncement(false,'Affiliate reached Admin create route');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){verifyAnnouncement($e->getStatusCode()===403,'Affiliate role denial was incorrect');}

$announcement->title='<script>alert(1)</script>';
$announcement->body="First line\n<script>unsafe</script>";
$html=view('announcements.partials.feed',['announcements'=>collect([$announcement])])->render();
verifyAnnouncement(str_contains($html,'&lt;script&gt;alert(1)&lt;/script&gt;') && !str_contains($html,'<script>'),'Announcement content was not escaped');
verifyAnnouncement(str_contains($html,'max-height:500px') && str_contains($html,'overflow-y:auto') && str_contains($html,'tabindex="0"'),'Feed is not bounded and keyboard scrollable');
verifyAnnouncement(str_contains($html,route('announcements.attachment',$announcement)),'Feed attachment link missing');
$empty=view('announcements.partials.feed',['announcements'=>collect()])->render();
verifyAnnouncement(str_contains($empty,'No announcements yet.'),'Empty feed message missing');
$form=view('announcements.partials.form',['action'=>route('announcements.store'),'submitLabel'=>'Post Announcement','errors'=>new Illuminate\Support\ViewErrorBag()])->render();
verifyAnnouncement(str_contains($form,'enctype="multipart/form-data"') && str_contains($form,'name="_token"') && substr_count($form,'name="type"')===5,'Create form contract incorrect');

$removeRequest=Request::create('/announcements/'.$announcement->id,'PUT',['title'=>'No attachment','type'=>'other','body'=>'Updated','remove_attachment'=>'1']);
$removeRequest->setLaravelSession($session);$app->instance('request',$removeRequest);$controller->update($removeRequest,$announcement);$announcement->refresh();
verifyAnnouncement(!$announcement->hasAttachment() && !Storage::disk('local')->exists($replacementPath),'Explicit attachment removal failed');
try{$controller->download($announcement);verifyAnnouncement(false,'Missing attachment downloaded');}catch(Symfony\Component\HttpKernel\Exception\HttpException $e){verifyAnnouncement($e->getStatusCode()===404,'Missing attachment did not return 404');}
// Reattach so deletion also exercises file cleanup.
$app->instance('request',$replaceRequest);$controller->update($replaceRequest,$announcement);$announcement->refresh();$replacementPath=$announcement->attachment_path;
$deleteResponse=$controller->destroy($announcement);
verifyAnnouncement($deleteResponse->isRedirect()&&Announcement::query()->count()===0,'Announcement deletion failed');
verifyAnnouncement(!Storage::disk('local')->exists($replacementPath),'Deleted announcement attachment was not cleaned up');
Storage::disk('local')->deleteDirectory('announcements');unlink($temp.'/source.pdf');unlink($temp.'/replacement.txt');rmdir($temp);
$migration->down();verifyAnnouncement(!$schema->hasTable('announcements')&&!$schema->hasColumn('permissions',Permissions::CREATE_ANNOUNCEMENTS),'Migration rollback incomplete');
echo "Passed {$checks} announcement assertions (isolated SQLite and private storage).\n";
