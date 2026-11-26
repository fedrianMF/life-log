# Testing

## Test Boundaries

- Test HTTP behavior with feature tests.
- Add focused service tests when a service has meaningful behavior that is clearer to exercise independently.
- Test authorization at the application boundary, including attempts to access another user's records.
- Assert observable behavior instead of implementation details.

## Test Data and Isolation

- Use the test database and model factories. Do not mock Eloquent or the query builder.
- Use framework fakes for files and external side effects.
- Create the data each test needs and freeze time when the expected result depends on it.

## Assertions

- For write operations, assert the response, persisted state, and relevant side effects.
- Prefer explicit expected values over assertions that only prove a result exists.
