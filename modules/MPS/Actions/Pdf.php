<?php

namespace Modules\MPS\Actions;

use Nesk\Puphpeteer\Puppeteer;

class Pdf
{
    public static function save($url, $path)
    {
        $puppeteer = new Puppeteer;
        $browser   = $puppeteer->launch();
        $page      = $browser->newPage();
        $page->goto($url, ['waitUntil' => 'networkidle0']);
        $page->pdf(['path' => $path, 'format' => 'A4']);
        $browser->close();
    }
}
