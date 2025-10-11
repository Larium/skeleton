<?php

declare(strict_types=1);

namespace Larium\Ui\Web\Provider;

use FastRoute\RouteCollector;
use Larium\Ui\Web\Action\HomeAction;

class RouterProvider
{
    public function register(RouteCollector $r): void
    {
        $r->get('/', HomeAction::class);
        // Add more routes as needed
        // $r->get('/about', AboutAction::class, 'about');
        // $r->post('/contact', ContactAction::class, 'contact');
    }
}
