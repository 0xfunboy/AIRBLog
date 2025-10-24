<?php
declare(strict_types=1);

namespace App\Controllers\Seo;

use App\Core\Controller;

final class RobotsController extends Controller
{
    public function index(): void
    {
        header('Content-Type: text/plain; charset=utf-8');
        $base = rtrim((string)config('app.url', ''), '/');
        $sitemap = ($base !== '' ? $base : '') . '/sitemap.xml';
        echo "User-agent: *\nDisallow: /admin\nSitemap: {$sitemap}\n";
    }
}
