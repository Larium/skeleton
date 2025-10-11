## Larium Framework: Agent Guide

Purpose: Help an autonomous agent understand and operate the Larium HTTP framework in this repository. This guide explains the runtime graph (DI), request lifecycle (middlewares → routing → actions → responders), shared kernel contracts (auth, validation), CommandBus (CQS), environment/config, and the extension points. Follow this to add features, debug, or automate tasks.

---

### 1) Runtime Overview
- App type: Web (PSR-7/PSR-15 HTTP). Entry is `public/web/index.php`.
- DI Container: PHP-DI via `src/Ui/Web/Provider/DiContainerProvider.php` (implements `Larium\Framework\Provider\ContainerProvider`).
- Router: FastRoute wrapped by `Larium\Framework\Bridge\Routing\FastRouteBridge`.
- Middleware pipeline:
  1. `ExceptionMiddleware` (outermost)
  2. `FirewallMiddleware` (auth by route patterns)
  3. `RoutingMiddleware`
  4. `ActionResolverMiddleware` (innermost)
- Views: Twig via `Larium\Bridge\Template\TwigTemplate` and `HtmlResponder`.

What happens per request:
1. Front controller bootstraps container and framework, wires middleware order.
2. `ExceptionMiddleware` guards everything, normalizes errors to HTML (4xx/5xx).
3. `FirewallMiddleware` checks path against configured patterns and applies authenticator service if needed; attaches `user` to request attributes.
4. Router matches path and HTTP method to an Action class.
5. Action executes (CQS-friendly), typically delegates rendering to `HtmlResponder`.

---

### 2) Dependency Injection (DI)
- Provider: `src/Ui/Web/Provider/DiContainerProvider.php` (implements `Larium\Framework\Provider\ContainerProvider`).
- Registers:
  - Router (FastRoute dispatcher built from `RouterProvider` routes)
  - Twig Template engine (`Template` abstraction)
  - Logger (Monolog → stdout)
  - Firewall + `FirewallMiddleware`
  - CommandBus (Tactician) with `ContainerLocator` from SharedKernel
- Extend by adding new service definitions in `addDefinitions([...])`.

Key factories:
- Router uses `RouterProvider` to register all HTTP routes programmatically.
- Template engine points to `src/Ui/Web/templates`.

---

### 3) Routing and Actions
- Router definitions live in `src/Ui/Web/Provider/RouterProvider.php`.
- Map HTTP paths to Action classes: `$r->get('/', HomeAction::class);`
- Action contract: `Larium\Framework\Http\Action` (invokable with `ServerRequestInterface`).
- Typical Action returns `HtmlResponder->getResponse(status, template, data)`.
- Add routes by updating `RouterProvider::register`.

---

### 4) Middleware Behavior
- `ExceptionMiddleware` (`src/Ui/Web/Middleware/ExceptionMiddleware.php`):
  - Catches all exceptions. Maps to `errors/4xx.html.twig` or `errors/5xx.html.twig`.
  - Logs unexpected errors.
- `FirewallMiddleware` (`src/Ui/Web/Middleware/FirewallMiddleware.php`):
  - Uses `SharedKernel\Authentication\Firewall` to map URL regex patterns → authenticator service ids.
  - If a path matches, invokes the authenticator service with the request.
  - Attaches the authentication result (e.g., principal) to request attribute `user`.
  - If no match, continues without authentication.
- Framework middlewares:
  - `RoutingMiddleware` matches the route.
  - `ActionResolverMiddleware` resolves the Action from DI and invokes it.

Order matters. Exception → Firewall → Routing → ActionResolver.

---

### 5) Responders and Templates
- `HtmlResponder` (`src/Ui/Web/Responder/HtmlResponder.php`) renders Twig templates and returns PSR-7 responses with `text/html`.
- Templates: `src/Ui/Web/templates` (e.g., `home/index.html.twig`, `error/4xx.html.twig`, `error/5xx.html.twig`).
- Set status codes and payload in the responder call.

---

### 6) Shared Kernel Contracts
- Location: `src/Ui/SharedKernel`
- Authentication: interfaces and helpers (`Authentication`, `AuthenticatorService`, `CredentialCollector`, `Firewall`).
  - `Firewall` maps regex patterns to authenticator service ids (defined in DI).
  - **Admin-only**: Authentication is for admin changes only, not user-specific authentication.
  - **Predefined types**: JWT, shared-key, basic-auth (configured in `.env.dist`).
- Validation: `Service/ValidationService.php` throws `ValidationException` with structured errors.
- CommandBus (CQS):
  - Tactician `CommandBus` registered in DI with middleware pipeline:
    - `CommandHandlerMiddleware(ClassNameExtractor, ContainerLocator, HandleInflector)`
  - `ContainerLocator` is in `SharedKernel/Service/ContainerLocator.php` and maps `FooCommand`→`FooHandler` (and `BarQuery`→`BarQueryHandler`).

---

### 7) Environment and Configuration
- `.env` loaded in `DiContainerProvider` via `vlucas/phpdotenv`.
- Important keys:
  - `APP_NAME`, `APP_ENV` (controls logger level, etc.)
  - Admin authentication secrets/keys for JWT, shared-key, basic-auth (see `.env.dist`)
- Assets copied via composer scripts to `public/web/*/vendor`.

---

### 8) How to Extend (for an agent)
- Add a new page:
  1) Create an Action in `src/Ui/Web/Action/`.
  2) Add a Twig template under `src/Ui/Web/templates/...`.
  3) Register route in `RouterProvider`.
- Protect a route:
  1) In `DiContainerProvider`, extend `Firewall` config with a regex → authenticator id.
  2) Register the authenticator service (and credential collector if needed).
  3) **Note**: Authentication is for admin-only routes, not user authentication.
- Add a command/query (CQS):
  1) Create `DoXCommand` + `DoXHandler` (or `FindYQuery` + `FindYQueryHandler`).
  2) No manual DI registration needed for concrete handlers: autowiring and `ContainerLocator` resolve them automatically. Register DI bindings only for interfaces (to select specific implementations).
  3) Get `CommandBus` from the container, call `handle(new DoXCommand(...))`.
- Add a service:
  - Define in `DiContainerProvider::getContainer()` via `$builder->addDefinitions([...])`.

---

### 9) Run and Verify
- Install deps: `composer install`
- Serve: `php -S 127.0.0.1:8000 -t public`
- Visit: `http://127.0.0.1:8000/web/`
- Lint/tests: `php -l src/Ui/Web/Action/HomeAction.php`, `./vendor/bin/phpunit` (if tests present)

---

### 10) Key Files Map
- Entry: `public/web/index.php`
- DI: `src/Ui/Web/Provider/DiContainerProvider.php` (implements `Larium\Framework\Provider\ContainerProvider`)
- Routes: `src/Ui/Web/Provider/RouterProvider.php`
- Middlewares: `src/Ui/Web/Middleware/*`
- Responder: `src/Ui/Web/Responder/HtmlResponder.php`
- Templates: `src/Ui/Web/templates/*`
- SharedKernel: `src/Ui/SharedKernel/*`
- CommandBus Locator: `src/Ui/SharedKernel/Service/ContainerLocator.php`

This guide is optimized for automation: the agent should be able to add routes, actions, services, auth rules, and commands by editing the files listed above, keeping middleware order and DI registrations intact.
