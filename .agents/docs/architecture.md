# Architecture

## Application Service Layer

- Use an Application Service Layer with thin controllers.
- Every controller action delegates its application use case to a service, including read and write operations.
- Controllers handle HTTP concerns only: receive the request, authorize the action, call a service, and return the response.
- Controllers must not contain Eloquent queries, data transformations, transactions, filesystem operations, or business rules.

## Layer Responsibilities

- Services own use-case orchestration, queries, transformations, persistence, transactions, filesystem operations, and side effects.
- Form Requests own validation and request-data normalization.
- Policies own authorization decisions.
- Models own relationships, casts, local invariants, and lifecycle behavior.
- Vue pages and components own presentation behavior.

## Form Requests

- Each model's CRUD uses a single Form Request, named `<Model>Request`, for both create (POST) and update (PUT/PATCH). Do not create separate `Store*Request` and `Update*Request` classes.
- Validate related data submitted with the model (nested attributes, related IDs, uploaded files) in that same request. Do not create a separate request per relation.
- Handle the differences between create and update inside the request, based on the route-bound model: authorization, update-only rules and `after()` checks.
- Scope related IDs to the parent model, so a request cannot reference another record's relations.

## Services

- Place application services in `app/Services` and name them after the domain concept they operate on.
- Give public methods names that describe complete use cases, such as `createEvent` or `getEventTimeline`.
- Inject services through the constructor when several methods need them, or through the controller method when only one action needs them. Do not use `app()` or `resolve()` when injection is possible.
- Use a database transaction when related writes must succeed or fail together.
- A service may contain closely related operations for one domain concept. Split it when it starts coordinating unrelated concepts or dependencies.
