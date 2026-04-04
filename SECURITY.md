# Security policy

## Supported versions

Security updates are applied to the active development branch (typically
`main`). Older branches may not receive backports unless maintainers explicitly
say otherwise.

| Version / branch | Supported          |
| ---------------- | ------------------ |
| `main`           | :white_check_mark: |
| Other branches   | :x: Best effort    |

## Reporting a vulnerability

**Please do not** open a public GitHub issue for security vulnerabilities.

Instead, report sensitive issues privately using one of these options:

1. **GitHub Security Advisories** (if the repository has them enabled): use
   *Security → Report a vulnerability* on the repository page.
2. **Email:** contact the repository maintainers at the address they publish in
   the repository profile or organization README. If none is listed, open a
   *non-sensitive* issue asking for a security contact.

Include:

- A short description of the issue and its impact
- Steps to reproduce (proof of concept if possible)
- Affected versions or commits if known
- Whether you believe the issue is already exploited in the wild

We aim to acknowledge reports within a few business days. Please allow time for
triage and a fix before public disclosure.

## Safe harbor

We support good-faith security research. If you follow this policy and act in
good faith, we will not pursue legal action for accidental, minor violations
during research. Do not access data that does not belong to you, and do not
degrade service for other users.

## Scope

In scope: this application’s codebase and its default configuration as shipped
in the repository.

Out of scope (report to the relevant vendor instead unless clearly caused by
our code): third-party services, dependency vulnerabilities already fixed in a
newer supported release, social engineering, or physical attacks.
