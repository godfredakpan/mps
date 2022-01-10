<?php

namespace App\Http\Controllers;

use Illuminate\Routing\Controller;

class UpdateController extends Controller
{
    public $hidden = true;

    public function composerUpdate()
    {
        chdir(base_path());
        set_time_limit(600);
        header('Content-type: text/plain');
        header('Cache-Control: no-cache');
        exec('php artisan composer:update', $output, $returnValue);
        echo "Returned with status $returnValue and output:\n\n";
        echo $output ? implode("\n", $output) : 'No output here';
        exit();
    }

    public function runMigration()
    {
        chdir(base_path());
        set_time_limit(600);
        header('Content-type: text/plain');
        header('Cache-Control: no-cache');
        exec('php artisan migrate', $output, $returnValue);
        echo "Returned with status $returnValue and output:\n\n";
        echo $output ? implode("\n", $output) : 'No output here';
        exit();
    }

    public function update($m)
    {
        return view('update', compact('m'));
    }
}
