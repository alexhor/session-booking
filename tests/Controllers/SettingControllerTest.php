<?php

namespace App\Controllers;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\DatabaseTestTrait;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Database\Seeds\SettingSeeder;
use Tests\Support\Database\Seeds\UserSeeder;
use App\Models\UserAuthentication;
use CodeIgniter\Events\Events;
use CodeIgniter\I18n\Time;
use CodeIgniter\Shield\Test\AuthenticationTesting;

class SettingControllerTest extends CIUnitTestCase
{
    protected $seed = UserSeeder::class;

    use DatabaseTestTrait;
    use FeatureTestTrait;
    use AuthenticationTesting;

    public function __construct($param)
    {
        parent::__construct($param);
        $this->settings = service('settings');
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->clearAuth();
        $this->settings->flush();
        $this->seed(SettingSeeder::class);
    }

    protected function clearAuth(): void
    {
        auth()->logout();
        session()->destroy();
        $this->assertNull(auth()->user());
    }

    public function testGetStringSetting(): void
    {
        $settingKey = 'Email.fromName';
        $value = $this->settings->get($settingKey);

        /** Public **/
        $this->settings->set('App.apiPublicSettingKeys', [$settingKey]);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'string'], json_decode($response->getJson(), true));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'string'], json_decode($response->getJson(), true));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'string'], json_decode($response->getJson(), true));

        /** Non public **/
        $this->clearAuth();
        $this->settings->set('App.apiPublicSettingKeys', []);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'string'], json_decode($response->getJson(), true));
    }

    public function testGetIntegerSetting(): void
    {
        $settingKey = 'Email.SMTPTimeout';
        $value = $this->settings->get($settingKey);

        /** Public **/
        $this->settings->set('App.apiPublicSettingKeys', [$settingKey]);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'integer'], json_decode($response->getJson(), true));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'integer'], json_decode($response->getJson(), true));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'integer'], json_decode($response->getJson(), true));

        /** Non public **/
        $this->clearAuth();
        $this->settings->set('App.apiPublicSettingKeys', []);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'integer'], json_decode($response->getJson(), true));
    }

    public function testGetEmailSetting(): void
    {
        $settingKey = 'Email.fromEmail';
        $value = $this->settings->get($settingKey);

        /** Public **/
        $this->settings->set('App.apiPublicSettingKeys', [$settingKey]);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'email'], json_decode($response->getJson(), true));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'email'], json_decode($response->getJson(), true));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'email'], json_decode($response->getJson(), true));

        /** Non public **/
        $this->clearAuth();
        $this->settings->set('App.apiPublicSettingKeys', []);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'email'], json_decode($response->getJson(), true));
    }

    public function testGetPasswordSetting(): void
    {
        $settingKey = 'Email.SMTPPass';
        $value = $this->settings->get($settingKey);

        /** Public **/
        $this->settings->set('App.apiPublicSettingKeys', [$settingKey]);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'password'], json_decode($response->getJson(), true));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'password'], json_decode($response->getJson(), true));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'password'], json_decode($response->getJson(), true));

        /** Non public **/
        $this->clearAuth();
        $this->settings->set('App.apiPublicSettingKeys', []);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => 'password'], json_decode($response->getJson(), true));
    }

    public function testGetArraySetting(): void
    {
        $settingKey = 'App.defaultLocale';
        $value = $this->settings->get($settingKey);

        /** Public **/
        $this->settings->set('App.apiPublicSettingKeys', [$settingKey]);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => setting('App.apiAllowedSettingKeys')[$settingKey]], json_decode($response->getJson(), true));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => setting('App.apiAllowedSettingKeys')[$settingKey]], json_decode($response->getJson(), true));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => setting('App.apiAllowedSettingKeys')[$settingKey]], json_decode($response->getJson(), true));

        /** Non public **/
        $this->clearAuth();
        $this->settings->set('App.apiPublicSettingKeys', []);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => setting('App.apiAllowedSettingKeys')[$settingKey]], json_decode($response->getJson(), true));
    }

    public function testGetCallbackSetting(): void
    {
        $settingKey = 'App.appTimezone';
        $this->settings->set('App.apiPublicSettingKeys', [$settingKey]);
        $value = $this->settings->get($settingKey);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => \DateTimeZone::listIdentifiers()], json_decode($response->getJson(), true));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => \DateTimeZone::listIdentifiers()], json_decode($response->getJson(), true));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => \DateTimeZone::listIdentifiers()], json_decode($response->getJson(), true));

        /** Non public **/
        $this->clearAuth();
        $this->settings->set('App.apiPublicSettingKeys', []);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertEqualsCanonicalizing(['value' => $value, 'validation' => \DateTimeZone::listIdentifiers()], json_decode($response->getJson(), true));
    }

    public function testGetForbiddenSetting(): void
    {
        $settingKey = 'Email.userAgent';
        $value = $this->settings->get($settingKey);

        /** Public **/
        $this->settings->set('App.apiPublicSettingKeys', [$settingKey]);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        $response->assertStatus(403);
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        $response->assertStatus(403);

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        $response->assertStatus(403);

        /** Non public **/
        $this->clearAuth();
        $this->settings->set('App.apiPublicSettingKeys', []);
        // No user logged in
        $response = $this->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        $response->assertStatus(401);
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        $response->assertStatus(401);

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        $response->assertStatus(403);
    }

    public function testSetStringSetting(): void
    {
        $settingKey = 'Email.fromName';
        $value = $this->settings->get($settingKey);
        $data = [
            'value' => 'Testing',
        ];
        $this->assertNotEquals($value, $data['value']);

        // No user logged in
        $response = $this->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertOk();
        $this->assertEquals($data['value'], $this->settings->get($settingKey));

        // Admin logged in - integer
        $data['value'] = 15;
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertOk();
        $this->assertEquals($data['value'], $this->settings->get($settingKey));

        // Admin logged in - empty value
        $data['value'] = '';
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertOk();
        $this->assertEquals('', $this->settings->get($settingKey));
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), []);
        $response->assertOk();
        $this->assertEquals('', $this->settings->get($settingKey));
    }

    public function testSetIntegerSetting(): void
    {
        $settingKey = 'Email.SMTPTimeout';
        $value = $this->settings->get($settingKey);
        $data = [
            'value' => 15,
        ];
        $this->assertNotEquals($value, $data['value']);

        // No user logged in
        $response = $this->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        //dd($response);
        $response->assertOk();
        $this->assertEquals($data['value'], $this->settings->get($settingKey));

        // Admin logged in - string integer
        $data['value'] = '345';
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertOk();
        $this->assertEquals($data['value'], $this->settings->get($settingKey));

        // Admin logged in - invalid value
        $data['value'] = 'invalid';
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertNotEquals($data['value'], $this->settings->get($settingKey));

        // Admin logged in - empty value
        $data['value'] = '';
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertNotEquals($data['value'], $this->settings->get($settingKey));
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), []);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertNotEquals($data['value'], $this->settings->get($settingKey));
    }

    public function testSetEmailSetting(): void
    {
        $settingKey = 'Email.fromEmail';
        $value = $this->settings->get($settingKey);
        $data = [
            'value' => 'test2@example.com',
        ];
        $this->assertNotEquals($value, $data['value']);

        // No user logged in
        $response = $this->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertOk();
        $this->assertEquals($data['value'], $this->settings->get($settingKey));

        // Admin logged in - invalid value
        $data['value'] = 'invalid';
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertNotEquals($data['value'], $this->settings->get($settingKey));

        // Admin logged in - empty value
        $data['value'] = '';
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertNotEquals($data['value'], $this->settings->get($settingKey));
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), []);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertNotEquals($data['value'], $this->settings->get($settingKey));
    }

    public function testSetPasswordSetting(): void
    {
        $settingKey = 'Email.SMTPPass';
        $value = $this->settings->get($settingKey);
        $data = [
            'value' => 'supersecret',
        ];
        $this->assertNotEquals($value, $data['value']);

        // No user logged in
        $response = $this->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertOk();
        $this->assertEquals($data['value'], $this->settings->get($settingKey));

        // Admin logged in - integer
        $data['value'] = 34552;
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertOk();
        $this->assertEquals($data['value'], $this->settings->get($settingKey));

        // Admin logged in - empty value
        $data['value'] = '';
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(404);
        $this->assertNotEquals($data['value'], $this->settings->get($settingKey));
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), []);
        $response->assertNotOk();
        $response->assertStatus(404);
        $this->assertNotEquals($data['value'], $this->settings->get($settingKey));
    }

    public function testSetArraySetting(): void
    {
        $settingKey = 'App.defaultLocale';
        $value = $this->settings->get($settingKey);
        $data = [
            'value' => 'en',
        ];
        $this->assertNotEquals($value, $data['value']);

        // No user logged in
        $response = $this->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertOk();
        $this->assertEquals($data['value'], $this->settings->get($settingKey));

        // Admin logged in - invalid string
        $data['value'] = 'invalid';
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertNotEquals($data['value'], $this->settings->get($settingKey));


        // Admin logged in - invalid integer
        $data['value'] = 2345;
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertNotEquals($data['value'], $this->settings->get($settingKey));

        // Admin logged in - empty value
        $array = $this->settings->set('App.apiAllowedSettingKeys');
        $array[$settingKey][] = '';
        $this->settings->set('App.apiAllowedSettingKeys', $array);
        $data['value'] = '';
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertOk();
        $this->assertEquals('', $this->settings->get($settingKey));
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), []);
        $response->assertOk();
        $this->assertEquals('', $this->settings->get($settingKey));
    }

    public function testSetCallableSetting(): void
    {
        $settingKey = 'App.appTimezone';
        $value = $this->settings->get($settingKey);
        $data = [
            'value' => 'Europe/Helsinki',
        ];
        $this->assertNotEquals($value, $data['value']);

        // No user logged in
        $response = $this->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertOk();
        $this->assertEquals($data['value'], $this->settings->get($settingKey));

        // Admin logged in - invalid value
        $data['value'] = 'invalid';
        $response = $this->actingAs($adminUser)->withBodyFormat('json')->put('settings/' . urlencode($settingKey), $data);
        $response->assertNotOk();
        $response->assertStatus(400);
        $this->assertNotEquals($data['value'], $this->settings->get($settingKey));
    }

    public function testDeleteSetting(): void
    {
        $settingKey = 'Email.fromName';
        $value = 'Something';
        $this->settings->set($settingKey, $value);

        // No user logged in
        $response = $this->withBodyFormat('json')->delete('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->delete('settings/' . urlencode($settingKey));
        $response->assertNotOk();
        $response->assertStatus(401);
        $this->assertEquals($value, $this->settings->get($settingKey));

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->delete('settings/' . urlencode($settingKey));
        $response->assertOk();
        $this->assertNotEquals($value, $this->settings->get($settingKey));
    }

    public function testGetWithValidationData(): void
    {
        // No user logged in
        $response = $this->get('settings/validation');
        $response->assertNotOk();
        $response->assertStatus(401);
        
        // User logged in
        $user = auth()->getProvider()->findById(2299488734);
        $response = $this->actingAs($user)->get('settings/validation');
        $response->assertNotOk();
        $response->assertStatus(401);

        // Admin logged in
        $adminUser = auth()->getProvider()->findById(772843);
        $response = $this->actingAs($adminUser)->get('settings/validation');
        $response->assertOk();
        $response_data = json_decode($response->getJson(), true);
        $this->assertIsArray($response_data);

        foreach(setting('App.apiAllowedSettingKeys') as $key => $validation) {
            $this->assertArrayHasKey($key, $response_data);
            $setting = $response_data[$key];
            $this->assertArrayHasKey('value', $setting);
            $this->assertEquals(setting($key), $setting['value']);
            $this->assertArrayHasKey('key', $setting);
            $this->assertEquals($key, $setting['key']);
            $this->assertArrayHasKey('validation', $setting);
            if (is_callable($validation)) $validation = $validation();
            $this->assertEquals($validation, $setting['validation']);
        }
    }
}
