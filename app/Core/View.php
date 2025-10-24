<?php
declare(strict_types=1);

namespace App\Core;

use App\Core\Container;

final class View
{
    public static function render(string $template, array $data = []): void
    {
        $config = Container::get('config', []);
        $viewPath = BASE_PATH . '/app/Views/' . $template . '.php';

        if (!is_file($viewPath)) {
            throw new \RuntimeException(sprintf('View "%s" not found.', $template));
        }

        extract($data, EXTR_SKIP);
        $appName = $config['app']['name'] ?? 'AIRewardrop';

        require $viewPath;
    }

    public static function renderPartial(string $template, array $data = []): void
    {
        $partialPath = BASE_PATH . '/app/Views/' . $template . '.php';

        if (!is_file($partialPath)) {
            throw new \RuntimeException(sprintf('Partial "%s" not found.', $template));
        }

        extract($data, EXTR_SKIP);

        require $partialPath;
    }
}
