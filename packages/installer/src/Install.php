<?php

namespace Tecdiary\Installer;

use App\Role;
use App\User;
use App\Helpers\Env;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
// use Modules\MPS\Models\User;
use Modules\MPS\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Encryption\Encrypter;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class Install
{
    public static function createDemoData()
    {
        set_time_limit(300);
        try {
            $demoData = Storage::disk('local')->get('demo.sql');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            $data = self::dbTransaction($demoData);
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return $data;
        } catch (\Exception $e) {
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    public static function createEnv()
    {
        if (is_file(base_path('.env.example'))) {
            File::copy(base_path('.env.example'), base_path('.env'));
        }

        Env::update([
            'APP_URL' => url('/'),
            'APP_KEY' => self::generateRandomKey(),
        ], false);
    }

    public static function createTables(Request $request, $data)
    {
        $result = self::isDbValid($data);
        if (!$result || $result['success'] == false) {
            return $result;
        }

        set_time_limit(300);
        $data['license']['item_id']         = '12';
        $data['license']['type']            = 'install';
        $data['license']['version']         = '0.6.0';
        $data['license']['domain']          = $request->root();
        $data['license']['installation_id'] = $data['installation_id'];

        $result = ['success' => false, 'message' => ''];
        $url    = 'https://be.tecdiary.net/api/v1/database';
        $client = new Client(['headers' => ['Accept' => 'application/json'], 'verify' => false]);
        $res    = $client->request('POST', $url, ['form_params' => $data['license']])->getBody()->getContents();
        $sql    = json_decode($res);

        if (empty($sql->database)) {
            $result = ['success' => false, 'message' => 'No database received from install server, please check with developer.'];
        } else {
            $result = self::dbTransaction($sql->database);
        }

        Storage::disk('local')->put('keys.json', '{ "mps": "' . $data['license']['code'] . '" }');
        return $result;
    }

    public static function createUser($userData)
    {
        $user                      = $userData;
        $user['owner']             = true;
        $user['phone']             = '0123456789';
        $user['password']          = Hash::make($userData['password']);
        $user['email_verified_at'] = now();
        $user['active']            = 1;
        $user                      = User::create($user);
        // $appuser                   = AppUser::find($user->id);
        $super_role = Role::create(['name' => 'super']);
        // $appuser->assignRole($super_role);
        $user->assignRole($super_role);
        Setting::updateOrCreate(['mps_key' => 'auto_update_time'], ['mps_value' => json_encode([
            'time'       => ['03:00', '22:00'],
            'checked_at' => now()->toDateString(),
            'day'        => ['mondays', 'tuesdays', 'wednesdays', 'thursdays', 'fridays', 'saturdays', 'sundays'][mt_rand(0, 6)],
        ])]);
    }

    public static function finalize()
    {
        Env::update(['APP_INSTALLED' => 'true', 'APP_DEBUG' => 'false', 'APP_URL' => url('/')], false);
        return true;
    }

    public static function isDbValid($data)
    {
        if (!File::exists(base_path('.env'))) {
            self::createEnv();
        }

        Env::update([
            'DB_HOST'     => $data['dbhost'],
            'DB_PORT'     => $data['dbport'],
            'DB_DATABASE' => $data['dbname'],
            'DB_USERNAME' => $data['dbuser'],
            'DB_PASSWORD' => $data['dbpass'] ?? '',
            'DB_SOCKET'   => $data['dbsocket'] ?? '',
        ], false);

        $result = false;
        config(['database.default' => 'mysql']);
        config(['database.connections.mysql.host' => $data['dbhost']]);
        config(['database.connections.mysql.port' => $data['dbport']]);
        config(['database.connections.mysql.database' => $data['dbname']]);
        config(['database.connections.mysql.username' => $data['dbuser']]);
        config(['database.connections.mysql.password' => $data['dbpass'] ?? '']);
        config(['database.connections.mysql.unix_socket' => $data['dbsocket'] ?? '']);

        try {
            DB::reconnect();
            DB::connection()->getPdo();
            if (DB::connection()->getDatabaseName()) {
                $result = ['success' => true, 'message' => 'Yes! Successfully connected to the DB: ' . DB::connection()->getDatabaseName()];
            } else {
                $result = ['success' => false, 'message' => 'DB Error: Unable to connect!'];
            }
        } catch (\Exception $e) {
            $result = ['success' => false, 'message' => 'DB Error: ' . $e->getMessage()];
        }

        return $result;
    }

    public static function registerLicense(Request $request, $licence)
    {
        $licence['windows']   = 0;
        $licence['item_id']   = '12';
        $licence['path']      = app_path();
        $licence['host']      = $request->url();
        $licence['domain']    = $request->root();
        $licence['full_path'] = public_path();
        $licence['referer']   = $request->path();

        $url    = 'https://be.tecdiary.net/api/v1/install';
        $client = new Client(['headers' => ['Accept' => 'application/json'], 'verify' => false]);
        return $client->request('POST', $url, ['form_params' => $licence])->getBody()->getContents();
    }

    public static function requirements()
    {
        $requirements = [];

        if (version_compare(phpversion(), '8.0', '<')) {
            $requirements[] = 'PHP 8.0 is required! Your PHP version is ' . phpversion();
        }

        if (ini_get('safe_mode')) {
            $requirements[] = 'Safe Mode needs to be disabled!';
        }

        if (ini_get('register_globals')) {
            $requirements[] = 'Register Globals needs to be disabled!';
        }

        if (ini_get('magic_quotes_gpc')) {
            $requirements[] = 'Magic Quotes needs to be disabled!';
        }

        if (!ini_get('file_uploads')) {
            $requirements[] = 'File Uploads needs to be enabled!';
        }

        if (!class_exists('PDO')) {
            $requirements[] = 'MySQL PDO extension needs to be loaded!';
        }

        if (!extension_loaded('openssl')) {
            $requirements[] = 'OpenSSL PHP extension needs to be loaded!';
        }

        if (!extension_loaded('tokenizer')) {
            $requirements[] = 'Tokenizer PHP extension needs to be loaded!';
        }

        if (!extension_loaded('imagick')) {
            $requirements[] = 'Imagick PHP extension needs to be loaded!';
        }

        if (!extension_loaded('mbstring')) {
            $requirements[] = 'Mbstring PHP extension needs to be loaded!';
        }

        if (!extension_loaded('curl')) {
            $requirements[] = 'cURL PHP extension needs to be loaded!';
        }

        if (!extension_loaded('ctype')) {
            $requirements[] = 'Ctype PHP extension needs to be loaded!';
        }

        if (!extension_loaded('xml')) {
            $requirements[] = 'XML PHP extension needs to be loaded!';
        }

        if (!extension_loaded('json')) {
            $requirements[] = 'JSON PHP extension needs to be loaded!';
        }

        if (!extension_loaded('zip')) {
            $requirements[] = 'ZIP PHP extension needs to be loaded!';
        }

        if (!is_writable(base_path('storage/app'))) {
            $requirements[] = 'storage/app directory needs to be writable!';
        }

        if (!is_writable(base_path('storage/framework'))) {
            $requirements[] = 'storage/framework directory needs to be writable!';
        }

        if (!is_writable(base_path('storage/logs'))) {
            $requirements[] = 'storage/logs directory needs to be writable!';
        }

        return $requirements;
    }

    protected static function dbTransaction($sql)
    {
        try {
            DB::unprepared(DB::raw($sql));
            $result = ['success' => true, 'message' => 'Database tables are created.'];
        } catch (\Exception $e) {
            $result = ['success' => false, 'SQL: unable to create tables, ' . $e->getMessage()];
        }

        return $result;
    }

    protected static function generateRandomKey()
    {
        return 'base64:' . base64_encode(Encrypter::generateKey(config('app.cipher')));
    }
}
