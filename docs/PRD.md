# APINEXA PRD

Tagline: Build. Govern. Document. Test.

## 1. Product Summary

APINEXA is a Git-based, AI-powered API Operating System for Laravel.

It gives Laravel developers a unified platform for API documentation, testing, governance, security, AI-assisted documentation, analytics, API keys, runtime schema registry, and team collaboration.

APINEXA is not a Swagger clone, Postman clone, or API gateway clone. It is a Laravel-native API lifecycle platform where API definitions live in Git, compile into a runtime registry, power documentation and testing, and can be enforced through permissions, payload rules, and signed API keys.

## 2. Problem Statement

Laravel teams commonly rely on disconnected tools for API documentation, request testing, API keys, access control, payload rules, analytics, and team communication. These tools drift away from the actual Laravel runtime and create duplicate work.

Developers spend too much time:

- Writing and updating API documentation.
- Maintaining Postman collections.
- Explaining endpoint behavior to teammates.
- Building internal API testing tools.
- Managing API keys.
- Implementing custom permissions.
- Controlling payload visibility and editability.
- Tracking API usage and failures.
- Keeping docs, code, validation rules, and runtime behavior aligned.

APINEXA solves this by making API definitions file-first, runtime-aware, AI-enhanced, and enforceable inside Laravel.

## 3. Vision

APINEXA should become the default API management platform for Laravel.

A developer should be able to run:

```bash
php artisan apinexa:install
```

and immediately gain:

- Live API documentation.
- Git-based API definitions.
- Runtime schema registry.
- AI-powered API explanations and examples.
- Secure API key management.
- API governance.
- API testing environment.
- API analytics.

## 4. Product Goals

### Primary Goals

- Provide one-command installation.
- Store API definitions in Git.
- Build a runtime schema registry.
- Generate live API documentation.
- Support AI-powered documentation and examples.
- Provide secure signed API keys.
- Keep the core database-optional.
- Offer a Laravel-native developer experience.
- Build an extensible architecture suitable for enterprise adoption.

### Secondary Goals

- Add API testing collections and environments.
- Add permission and payload governance.
- Add analytics and reporting.
- Support team collaboration.
- Support multi-project and SaaS use cases.
- Enable a plugin ecosystem.

## 5. Non-Goals

APINEXA will not:

- Replace Laravel routing.
- Replace Laravel authentication.
- Require a database for core API definitions.
- Use OpenAPI as the internal source of truth.
- Automatically trust AI-generated security policy.
- Become a hosted API gateway in the open-source core.
- Automatically rewrite application controllers.

## 6. Core Philosophy

APINEXA is:

- File-first.
- Git-friendly.
- Runtime-driven.
- AI-enhanced.
- Cache-first.
- Laravel-native.
- Extensible.
- Secure by default.

APINEXA is not database-driven. Files are the source of truth. The database is used only for optional analytics, logs, metrics, API key audit records, and personal testing history.

## 7. Target Users

### Individual Developers

Laravel developers who need fast API documentation, testing, and key management without stitching together multiple tools.

### Teams

Engineering teams maintaining production APIs who need governance, consistency, and runtime visibility.

### Agencies

Agencies delivering APIs for clients who need repeatable setup, documentation, and handoff workflows.

### SaaS Platforms

SaaS products that expose public or partner APIs and need API keys, scopes, docs, analytics, and lifecycle governance.

## 8. Product Positioning

APINEXA is not:

- Swagger.
- Postman.
- A generic API gateway.
- A static docs generator.

APINEXA is:

> The API Operating System for Laravel.

## 9. Storage Strategy

### Source of Truth

Files stored in Git.

Recommended default structure:

```text
api-nexa/
├── schemas/
├── permissions/
├── collections/
├── examples/
└── fragments/
```

### Cache

Redis or Laravel cache stores compiled runtime artifacts:

- Registry snapshots.
- Endpoint metadata.
- Documentation models.
- Permission indexes.
- Payload policy indexes.
- AI response cache.
- API key revocation lists.

### Database

Optional only.

Used for:

- Analytics.
- Logs.
- Metrics.
- AI usage tracking.
- API key audit records.
- Personal request history.

## 10. Core Modules

### Module 1: API Registry

Responsibilities:

- Discover schema files.
- Load schemas.
- Validate schemas.
- Cache compiled schemas.
- Expose runtime endpoint metadata.
- Support cache invalidation.
- Support hot reload during local development.

### Module 2: Schema Engine

Responsibilities:

- API-as-code definitions.
- Schema validation.
- Schema versioning.
- Schema composition.
- Schema inheritance.
- Shared fragments.
- Lifecycle metadata.

Example schema:

```php
<?php

return [
    'name' => 'Create Job',
    'method' => 'POST',
    'endpoint' => '/jobs',
    'version' => 'v1',
    'auth' => true,
    'roles' => ['admin', 'employer'],
    'payload' => [
        'title' => 'required|string',
        'salary' => 'nullable|numeric',
    ],
];
```

### Module 3: Documentation Engine

Responsibilities:

- Generate live documentation.
- Inspect routes, controllers, form requests, resources, and schemas.
- Export OpenAPI.
- Provide Swagger UI.
- Export Markdown.
- Export HTML.
- Export JSON.
- Export PDF.

Documentation must include:

- Endpoint name.
- Method and URI.
- Description.
- Request payload.
- Response examples.
- Validation rules.
- Auth requirements.
- Roles and scopes.
- Errors.
- Version and lifecycle status.

### Module 4: AI Engine

Supported providers:

- OpenAI.
- Anthropic.
- Gemini.
- OpenRouter.

Capabilities:

- Generate endpoint documentation.
- Generate request examples.
- Generate response examples.
- Explain endpoints.
- Explain validation rules.
- Explain errors.
- Generate SDK snippets.
- Generate test cases.

Requirements:

- Users provide their own AI keys.
- AI output is cached.
- AI usage is tracked.
- Secrets are redacted before prompts.
- AI cannot define or override security policy automatically.

### Module 5: API Keys

Capabilities:

- Signed API keys.
- Test and live keys.
- Scopes.
- Endpoint permissions.
- Expiration.
- Rotation.
- Revocation.
- Rate limiting.
- Optional audit records.

The API key engine must not perform a database lookup on every request. Signed claims and cache-backed revocation checks should be used for fast runtime verification.

### Module 6: Permissions

Capabilities:

- Role permissions.
- Endpoint permissions.
- Field permissions.
- Payload permissions.
- Scope permissions.

Permission decisions must be deterministic, testable, and deny-by-default.

### Module 7: Payload Engine

Capabilities:

- Hide fields.
- Inject fields.
- Transform fields.
- Override fields.
- Validate fields dynamically.
- Mask sensitive values.
- Reject unknown fields when configured.

Example:

```php
'payload_policy' => [
    'salary' => [
        'visible_to' => ['admin'],
    ],
    'tenant_id' => [
        'inject' => 'auth.user.tenant_id',
        'editable_by' => [],
    ],
    'title' => [
        'transform' => ['trim', 'uppercase'],
    ],
]
```

### Module 8: API Testing

Capabilities:

- Request builder.
- Collections.
- Folders.
- Environment variables.
- Saved requests.
- Request history.
- Import and export.
- Run as role.
- Run as API key.

The testing module should feel Laravel-native and registry-aware, not like a generic Postman clone.

### Module 9: Analytics

Capabilities:

- Endpoint usage metrics.
- Response time tracking.
- Error tracking.
- Status code reporting.
- API key activity.
- Permission denial events.
- Payload policy violation events.

Storage drivers:

- Disabled.
- Log.
- Database.
- Redis.
- Custom.

### Module 10: Filament Admin Panel

Sections:

- Dashboard.
- Documentation.
- Testing.
- Schemas.
- API Keys.
- Permissions.
- Analytics.
- AI Settings.
- System Settings.

Filament should be the default admin interface, while the package core remains usable without forcing every application to use Filament.

## 11. CLI Commands

Required commands:

```bash
php artisan apinexa:install
php artisan apinexa:scan
php artisan apinexa:docs
php artisan apinexa:test
php artisan apinexa:key:create
php artisan apinexa:key:revoke
php artisan apinexa:cache
php artisan apinexa:clear
```

Command expectations:

- `APINEXA:install` publishes config, stubs, migrations, prompts, and optional Filament resources.
- `APINEXA:scan` inspects routes, controllers, form requests, and resources.
- `APINEXA:docs` generates documentation exports.
- `APINEXA:test` runs generated or selected API tests.
- `APINEXA:key:create` creates signed API keys.
- `APINEXA:key:revoke` revokes API keys through cache or optional database records.
- `APINEXA:cache` compiles the runtime registry.
- `APINEXA:clear` clears registry, docs, AI, and analytics caches.

## 12. MVP Scope: Version 1

Included:

- API Registry.
- Schema Engine.
- Documentation Engine.
- API Keys.
- AI Documentation.
- Install command.
- Cache and clear commands.
- OpenAPI, Markdown, HTML, and JSON exports.
- Basic Filament documentation and settings pages.
- Pest test suite.

Excluded from Version 1:

- Full Analytics.
- Team Collaboration.
- SaaS Features.
- Plugin Marketplace.
- Advanced payload governance UI.
- Contract diff UI.
- SDK generation beyond AI-generated snippets.

## 13. Future Roadmap

### Version 1

API Registry, Schema Engine, Documentation Engine, API Keys, and AI Documentation.

### Version 2

Permissions and Payload Engine.

### Version 3

API Testing Platform with collections, environments, variables, history, and import/export.

### Version 4

Analytics with database and Redis drivers.

### Version 5

Team Collaboration with reviews, comments, ownership, and audit trails.

### Version 6

SaaS Platform support, multi-project workspaces, hosted docs portals, and advanced enterprise governance.

## 14. Functional Requirements

- Users can install APINEXA with one Artisan command.
- Users can define API schemas in Git-tracked files.
- Users can compile schemas into a runtime registry.
- Users can generate API documentation from schemas and Laravel runtime metadata.
- Users can export documentation in multiple formats.
- Users can configure AI providers with their own keys.
- Users can generate AI-assisted documentation and examples.
- Users can issue signed API keys.
- Users can revoke keys without requiring request-time database lookups.
- Users can define endpoint, role, scope, field, and payload permissions.
- Users can define payload transformation and visibility rules.
- Users can test APIs from an admin UI.
- Users can track usage analytics when enabled.

## 15. Non-Functional Requirements

- PHP 8.3+ support.
- Laravel 11 and Laravel 12 support.
- Core features must work without database tables.
- Runtime registry lookup should be cache-first.
- Public extension points must use contracts.
- Security-sensitive modules must be strongly tested.
- Package must use sensible defaults.
- Configuration must be publishable.
- Filament integration must be modular.
- AI integration must redact secrets and avoid automatic writeback.

## 16. Security Requirements

APINEXA must address:

- API key signing.
- API key leakage.
- Permission enforcement.
- Payload restrictions.
- Rate limiting.
- AI prompt injection.
- XSS protection.
- CSRF protection.
- SSRF protection.
- Command injection.
- Mass assignment.
- Permission escalation.

Baseline controls:

- Escape rendered docs content.
- Sanitize Markdown.
- Redact secrets from logs, analytics, tester history, and AI prompts.
- Deny by default when policy is missing or ambiguous.
- Restrict tester requests to local application routes by default.
- Require explicit confirmation for destructive tester requests.
- Store only API key prefixes and hashes when persistence is enabled.

## 17. Success Metrics

- Installation completes in under 5 minutes.
- First documented endpoint can be created in under 15 minutes.
- Documentation generation completes in under 30 seconds for typical projects.
- Core features require zero database tables.
- Laravel 11 and Laravel 12 are supported.
- Runtime registry lookup stays under 1 ms p95 when cached.
- API key verification avoids per-request database lookup.
- Critical security module test coverage is near 100%.
- Package architecture supports plugins and future Pro features.

## 18. Open Source and Future Pro Split

Open-source core:

- File-based schemas.
- Runtime registry.
- Documentation generation.
- OpenAPI, Markdown, HTML, JSON exports.
- Signed API keys.
- AI provider abstraction.
- Basic Filament panel.
- Basic testing and package commands.

Future Pro:

- Advanced analytics dashboards.
- Team collaboration.
- Approval workflows.
- Contract diff UI.
- Breaking change detection.
- Hosted docs portals.
- Multi-project workspaces.
- Advanced audit logs.
- SSO/SAML.
- SDK generation.
- Enterprise compliance reports.

## 19. Acceptance Criteria for PRD Completion

This PRD is complete when it defines:

- Product identity.
- Problem and vision.
- Target users.
- Goals and non-goals.
- Core philosophy.
- Storage strategy.
- Core modules.
- CLI surface.
- MVP scope.
- Roadmap.
- Functional and non-functional requirements.
- Security requirements.
- Success metrics.
- Open-source and future Pro positioning.


