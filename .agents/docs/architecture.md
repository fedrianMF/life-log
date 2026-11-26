# Application Architecture

This document records Life Log's evolving house conventions for Laravel backend code. It complements Laravel's conventions, the repository `AGENTS.md`, and the installed Laravel skills; it does not repeat their general framework guidance.

When rules conflict, follow this order:

1. Explicit instructions for the current task.
2. The root `AGENTS.md` project guidelines.
3. This document.
4. General Laravel and skill guidance.

Add conventions here when they represent an intentional, repeatable project decision. Do not turn a one-off implementation detail into a global rule.

## Domain architecture

- Events are the source of truth. Daily summaries are derived data and can always be regenerated.
- Store timestamps in UTC. Compute the user's day with `config('app.user_timezone')` (`America/La_Paz`).
- Store money as integer cents plus an ISO currency. The default currency is BOB.
- During the trial, type-specific event fields live in `events.data` (`jsonb`). Keep validation rules for each type in one place; do not build generic form builders.

## Application services

- `app/Services` is the approved location for application services.
- Controllers handle HTTP concerns: receive an authorized and validated request, call an application service, and return the response.
- Controllers delegate create, update and delete use cases to a service. Read-only presentation queries may remain in a controller while they are simple and specific to one page.
- Services coordinate application operations, Eloquent writes, transactions and filesystem side effects. Keep validation in Form Requests and ownership rules in Policies.
- Keep model-specific behavior on the model when it belongs to that model's lifecycle, casts, relationships or invariants.
- Inject services through constructors when several controller methods use them, or through controller methods when only one action needs them. Do not resolve dependencies with `app()` or `resolve()` when injection is possible.
- Use a database transaction in the service when one use case must persist several related changes atomically. Do not add a transaction around a single independent write without a concrete reason.
- A service may expose several closely related operations for one domain concept. Split it when it starts coordinating unrelated concepts or acquires unrelated dependencies.

### Service naming

- Name services after the domain concept they operate on, such as `EventService` or `AttachmentService`.
- Name public service methods with a verb and a clear subject, such as `createEvent`, `updateEvent`, `deleteEvent`, `storeAttachments` or `removeAttachment`.
- Avoid vague method names such as `handle`, `process`, `execute` or `run` unless an implemented contract or established framework convention requires them.
- Use descriptive dependency names such as `$eventService`, not `$service`.

## Method design

- Every method has one responsibility and one clear level of abstraction.
- Keep methods understandable at a glance. When a method mixes validation, transformation, persistence and side effects, move the application operation into a service and extract named private methods for distinct steps.
- Prefer early returns to deeply nested conditionals.
- Extract a method when the extracted name communicates a domain concept or removes meaningful duplication. Do not split straightforward code into tiny methods that only restate individual statements.
- Keep public methods focused on orchestration. Put implementation details in well-named private methods when they are not part of the class's public contract.
- Use explicit parameter and return types. Use PHPDoc array shapes when an array has a known structure.

## Naming

- Follow Laravel conventions: classes use StudlyCase; methods and PHP variables use camelCase; database columns and request keys use snake_case.
- Names must communicate intent and behavior. Prefer `$validatedEventData`, `$attachmentPaths` and `$eventsForDay` over `$data`, `$items` and `$result` when the broader names are ambiguous.
- Method names start with a verb that describes the observable action, such as `createEvent`, `convertToUtc`, `storePhotos` or `markSummaryStale`.
- Boolean variables and methods start with `is`, `has`, `can`, `should` or another predicate, such as `$hasDuration` or `canViewAttachment`.
- Use singular names for one model or value and plural names for collections: `$event` and `$events`, `$attachment` and `$attachments`.
- Include units in names when they matter: `$amountCents`, `$durationMinutes`, `$distanceKm`.
- Keep Laravel's conventional method names where the framework gives them meaning, including resource controller actions, relationship methods, `casts`, `rules` and model lifecycle methods.
- Avoid abbreviations unless they are established domain terms. Prefer `$eventType` over `$type` when the shorter name could be ambiguous.

## Service boundaries and tests

- Test behavior through the public boundary that owns it. HTTP behavior belongs in feature tests; independently meaningful service behavior may have focused service tests.
- Do not mock Eloquent or the query builder. Use the test database and factories.
- Test transactions and side effects as one application outcome when the service coordinates them.
- Authorization tests must still prove that one user cannot operate on another user's records, even when the write is delegated to a service.

## Growing these conventions

- Before adding a rule, inspect repeated code and decide whether it is an intentional house pattern.
- Add the smallest rule that resolves the recurring decision. Include an example only when wording alone could be interpreted in multiple ways.
- Update existing rules when the project changes direction instead of leaving conflicting alternatives.
