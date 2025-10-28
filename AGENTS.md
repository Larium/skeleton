## Larium Framework: Agent Guide

Purpose: Help an autonomous agent understand and operate the Larium HTTP framework in this repository. This guide explains the runtime graph (DI), request lifecycle (middlewares → routing → actions → responders), shared kernel contracts (auth, validation), CommandBus (CQS), environment/config, and the extension points. Follow this to add features, debug, or automate tasks.

---

### 1) Runtime Overview
- **App types**: **Web (HTML)** and **Api (JSON)** - two independent applications sharing common infrastructure.
- **Web entry**: `public/web/index.php` - HTML responses via Twig templates.
- **Api entry**: `public/api/index.php` - JSON responses.
- **DI Containers**: Each interface has its own provider:
  - Web: `src/Ui/Web/Provider/DiContainerProvider.php`
  - Api: `src/Ui/Api/Provider/DiContainerProvider.php`
- **Router**: FastRoute wrapped by `Larium\Framework\Bridge\Routing\FastRouteBridge`.
- **Middleware pipeline** (same for both applications):
  1. `ExceptionMiddleware` (outermost) - interface-specific
  2. `FirewallMiddleware` (auth by route patterns) - shared
  3. `RoutingMiddleware` - framework
  4. `ActionResolverMiddleware` (innermost) - framework
- **Views**: Twig via `HtmlResponder` (Web), JSON via `JsonResponder` (Api).

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
- `ExceptionMiddleware` (interface-specific):
  - Web: `src/Ui/Web/Middleware/ExceptionMiddleware.php` - catches all exceptions, maps to `errors/4xx.html.twig` or `errors/5xx.html.twig`.
  - Api: `src/Ui/Api/Middleware/ExceptionMiddleware.php` - catches all exceptions, returns JSON.
  - Both delegate to `ExceptionErrorMapper` (`src/Ui/SharedKernel/Error/ExceptionErrorMapper.php`) for centralized exception-to-status/payload mapping.
  - Logs unexpected errors (status >= 500).
- `FirewallMiddleware` (`src/Ui/SharedKernel/Middleware/FirewallMiddleware.php`):
  - Uses `SharedKernel\Authentication\Firewall` to map URL regex patterns → authenticator service ids.
  - If a path matches, invokes the authenticator service with the request.
  - Attaches the authentication result (e.g., principal) to request attribute `user`.
  - If no match, continues without authentication.
  - **Note**: Shared across both Web and Api applications.
- Framework middlewares:
  - `RoutingMiddleware` matches the route.
  - `ActionResolverMiddleware` resolves the Action from DI and invokes it.

Order matters. Exception → Firewall → Routing → ActionResolver.

---

### 5) Responders and Templates
- `HtmlResponder` (`src/Ui/Web/Responder/HtmlResponder.php`) renders Twig templates and returns PSR-7 responses with `text/html`.
  - Signature: `getResponse(int $status, string $template, array $payload = [], ?Throwable $e = null)`
- `JsonResponder` (`src/Ui/Api/Responder/JsonResponder.php`) renders JSON and returns PSR-7 responses with `application/json`.
  - Signature: `getResponse(array $payload, int $status)`
- Templates: `src/Ui/Web/templates` (e.g., `home/index.html.twig`, `errors/4xx.html.twig`, `errors/5xx.html.twig`).
- Set status codes and payload in the responder call.

---

### 6) Shared Kernel Contracts
- Location: `src/Ui/SharedKernel`
- **Middleware**:
  - `FirewallMiddleware` (`Middleware/FirewallMiddleware.php`) - shared auth middleware for both interfaces.
- **Error Handling**:
  - `ExceptionErrorMapper` (`Error/ExceptionErrorMapper.php`) - centralizes exception-to-status/payload mapping.
    - Returns `['status' => int, 'payload' => array]` for consistent error responses.
    - Handles `AuthenticationException` (401), `ValidationException` (400), `HttpNotFoundException` (404), `HttpMethodNotAllowedException` (405), and generic `Throwable` (500).
- **Authentication**: interfaces and helpers (`Authentication`, `AuthenticatorService`, `CredentialCollector`, `Firewall`).
  - `Firewall` maps regex patterns to authenticator service ids (defined in each interface's DI).
  - **Admin-only**: Authentication is for admin changes only, not user-specific authentication.
  - **Predefined types**: JWT, shared-key, basic-auth (configured in `.env.dist`).
- **Validation**: `Service/Validation/ValidationService.php` throws `ValidationException` with structured errors.
  - Uses Symfony Validator: `validate(object $object, array $context = [])` throws `ValidationException` with field-level errors.
  - `ValidationException::badRequest(array $errors)` creates 400-level exception with `['reason' => $message, 'name' => $propertyPath]` structure.
  - `JsonIntegrityValidation` validates JSON syntax and returns decoded array, throws `ValidationException` on parse errors.
- **Request Object Provider**: `Service/RequestObjectProvider.php` maps HTTP requests to DTOs.
  - `provide(ServerRequestInterface $request, string $className, array $validationContext): object` auto-detects content type.
  - Supports JSON (`application/json`) and form data (parsed body).
  - Merges query parameters with body (body values override query on conflicts).
  - Handles validation context for DTO validation rules.
- **CommandBus (CQS)**:
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
- **Add a new Web page**:
  1) Create an Action in `src/Ui/Web/Action/`.
  2) Add a Twig template under `src/Ui/Web/templates/...`.
  3) Register route in `src/Ui/Web/Provider/RouterProvider.php`.
  4) Return `HtmlResponder->getResponse($status, $template, $data)`.
- **Add a new Api endpoint**:
  1) Create an Action in `src/Ui/Api/Action/`.
  2) Register route in `src/Ui/Api/Provider/RouterProvider.php`.
  3) Return `JsonResponder->getResponse($payload, $status)`.
- **Protect a route** (both interfaces):
  1) In interface-specific `DiContainerProvider`, extend `Firewall` config with a regex → authenticator id.
  2) Register the authenticator service (and credential collector if needed).
  3) `FirewallMiddleware` will automatically apply authentication if URL matches pattern.
  4) **Note**: Authentication is for admin-only routes, not user authentication.
- **Add a command/query (CQS)**:
  1) Create `DoXCommand` + `DoXHandler` (or `FindYQuery` + `FindYQueryHandler`).
  2) No manual DI registration needed for concrete handlers: autowiring and `ContainerLocator` resolve them automatically. Register DI bindings only for interfaces (to select specific implementations).
  3) Get `CommandBus` from the container, call `handle(new DoXCommand(...))`.
- **Add a service**:
  - Define in interface-specific `DiContainerProvider::getContainer()` via `$builder->addDefinitions([...])`.
  - Each interface's DI container is independent.
- **Map request data to a DTO**:
  1) Create a DTO class with properties and validation attributes (Symfony Validator).
  2) In your Action, inject `RequestObjectProvider` from SharedKernel.
  3) Call `$provider->provide($request, MyDto::class, ['validation_context'])`.
  4) The provider auto-detects JSON vs form data, merges query+body, validates, and returns the DTO.

---

### 9) Run and Verify
- Install deps: `composer install`
- Serve: `php -S 127.0.0.1:8000 -t public`
- **Web interface**: `http://127.0.0.1:8000/web/`
- **Api interface**: `http://127.0.0.1:8000/api/`
- Lint/tests: `php -l src/Ui/Web/Action/HomeAction.php`, `./vendor/bin/phpunit` (if tests present)

---

### 10) Key Files Map

**Web Application:**
- Entry: `public/web/index.php`
- DI: `src/Ui/Web/Provider/DiContainerProvider.php`
- Routes: `src/Ui/Web/Provider/RouterProvider.php`
- Middlewares: `src/Ui/Web/Middleware/*`
- Actions: `src/Ui/Web/Action/*`
- Responder: `src/Ui/Web/Responder/HtmlResponder.php`
- Templates: `src/Ui/Web/templates/*`

**Api Application:**
- Entry: `public/api/index.php`
- DI: `src/Ui/Api/Provider/DiContainerProvider.php`
- Routes: `src/Ui/Api/Provider/RouterProvider.php`
- Middlewares: `src/Ui/Api/Middleware/*`
- Actions: `src/Ui/Api/Action/*`
- Responder: `src/Ui/Api/Responder/JsonResponder.php`

**Shared Kernel:**
- Middleware: `src/Ui/SharedKernel/Middleware/FirewallMiddleware.php`
- Error: `src/Ui/SharedKernel/Error/ExceptionErrorMapper.php`
- Request Object Provider: `src/Ui/SharedKernel/Service/RequestObjectProvider.php`
- Validation Services: `src/Ui/SharedKernel/Service/Validation/*`
- CommandBus Locator: `src/Ui/SharedKernel/Service/ContainerLocator.php`
- Authentication: `src/Ui/SharedKernel/Authentication/*`

This guide is optimized for automation: the agent should be able to add routes, actions, services, auth rules, and commands by editing the files listed above, keeping middleware order and DI registrations intact.
