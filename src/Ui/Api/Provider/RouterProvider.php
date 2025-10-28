<?php

declare(strict_types=1);

namespace Larium\Ui\Api\Provider;

use FastRoute\RouteCollector;
use Larium\Ui\Api\Action\HomeAction;

class RouterProvider
{
    public function register(RouteCollector $r): void
    {
        $r->get('/', HomeAction::class);
        // Add more routes as needed
    }
}

