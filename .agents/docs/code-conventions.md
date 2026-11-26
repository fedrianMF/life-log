# Code Conventions

## Method Design

- Give every method one responsibility and one clear level of abstraction.
- Keep methods understandable at a glance and prefer early returns to deeply nested conditionals.
- Extract a method when its name communicates a meaningful concept or removes meaningful duplication. Do not fragment straightforward code into one-line methods.
- Keep public methods focused on orchestration and move implementation details to descriptive private methods.

## Naming

- Follow Laravel naming conventions: classes use StudlyCase, methods and PHP variables use camelCase, and database columns and request keys use snake_case.
- Use descriptive names that communicate intent. Avoid vague names such as `$data`, `$items`, `$result`, `handle`, or `process` when the context does not make their meaning clear.
- Start action methods with a verb and boolean names with a predicate such as `is`, `has`, `can`, or `should`.
- Use singular names for one value and plural names for collections.
- Include units when they matter, such as `$amountCents`, `$durationMinutes`, or `$distanceKm`.
- Preserve method names that have framework meaning, including resource controller actions, relationship methods, `casts`, and `rules`.
- Avoid abbreviations unless they are established domain terms.

## Types

- Declare parameter and return types explicitly.
- Use PHPDoc array shapes when an array has a known structure.
