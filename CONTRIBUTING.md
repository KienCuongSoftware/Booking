# Contributing to Booking

Thank you for your interest in improving this project. This document explains
how we prefer to receive contributions.

## Before you start

- Search the issue tracker and existing pull requests to avoid duplicate work.
- For large changes, open an issue first to discuss the approach.

## Development setup

Follow the steps in [README.md](README.md) to run the application locally. Use a
dedicated database for development; do not commit secrets or production data.

## Workflow

1. **Fork** the repository (if you do not have write access).
2. **Create a branch** from `main` with a short, descriptive name:

   - `feat/short-description`
   - `fix/short-description`
   - `docs/short-description`

3. **Make focused commits** with clear messages (imperative mood, English), for
   example: `Add pagination to host room types index`.
4. **Open a pull request** into `main`. Use [PR_DESCRIPTION.md](PR_DESCRIPTION.md)
   as a guide for what to include in the description.

## Code style

- **PHP:** Run Laravel Pint before pushing:

  ```bash
  ./vendor/bin/pint
  ```

- **Blade / front-end:** Match existing Tailwind and component patterns; avoid
  unrelated reformatting in the same commit as functional changes.

## Tests

- Add or update tests when behavior changes.
- Ensure tests pass:

  ```bash
  php artisan test
  ```

## Database changes

- New schema changes belong in **migrations** with reversible `down()` methods
  when practical.
- Do not commit `.env` or local database dumps.

## Licensing

By contributing, you agree that your contributions will be licensed under the
same license as the project ([LICENSE](LICENSE)).
