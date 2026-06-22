# ApiForge Architecture

## 1. Purpose

This document defines the architecture for ApiForge, a Git-based, AI-powered API Operating System for Laravel.

The goal is to prevent the package from becoming a tangled collection of controllers, helpers, and admin screens. Every major feature must live behind clear module boundaries, contracts, cache strategies, and runtime responsibilities.

ApiForge must stay:

- File-first.
- Runtime-driven.
- Cache-optimized.
- AI-enhanced.
- Plugin-extensible.
- Database-optional.
- Laravel-native at the edges.
- Framework-independent where practical in the core.

## 2. High-Level Architecture

```text
Developer
    ↓
Schema Files
    ↓
Schema Loader
    ↓
Schema Validator
    ↓
Schema Normalizer
    ↓
Runtime Registry
    ↓
Permission Engine
    ↓
Payload Engine
    ↓
Controller Execution
    ↓
Response Processor
    ↓
Documentation Engine
    ↓
Analytics Engine
```

Runtime request flow and documentation flow share the same source of truth: compiled schema metadata inside the runtime registry.

## 3. Architecture Principles

### 3.1 File First

API definitions live in files under `api-forge/`. Git is the source of truth. Database rows may support logs, metrics, history, or audit records, but they must not become the primary source for endpoint definitions.

### 3.2 Runtime Driven

ApiForge is not only a static documentation generator. Runtime middleware uses the registry to identify endpoints, validate access, apply payload policy, rate limit API keys, and record usage.

### 3.3 Cache Optimized

Schema loading and documentation generation can be expensive. Production should use compiled registry snapshots and cache-backed indexes.

### 3.4 AI Enhanced

AI assists documentation, examples, explanations, test cases, and snippets. AI is never the authority for security policy, permissions, or validation.

### 3.5 Plugin Extensible

Exporters, AI providers, analytics drivers, schema processors, payload transformers, and Filament extensions should be replaceable through contracts.

### 3.6 Database Optional

Core features must work without migrations:

- Schema loading.
- Registry lookup.
- Documentation generation.
- API key verification.
- Permission checks.
- Payload policy checks.

Database is optional for analytics, audit records, AI usage, key metadata, and personal request history.

## 4. Package Structure

```text
src/
├── ApiForge.php
├── ApiForgeServiceProvider.php
├── Analytics/
├── AI/
├── Auth/
├── Commands/
├── Contracts/
├── Core/
├── Documentation/
├── Events/
├── Exceptions/
├── Filament/
├── Middleware/
├── Payload/
├── Permissions/
├── Plugins/
├── Registry/
├── Schema/
├── Security/
├── Support/
└── Testing/
```

Supporting package structure:

```text
config/
└── api-forge.php

database/
└── migrations/

resources/
├── prompts/
├── stubs/
└── views/

routes/
├── api.php
└── web.php

tests/
├── Feature/
├── Integration/
├── Performance/
└── Unit/
```

Install command generated structure:

```text
api-forge/
├── schemas/
├── permissions/
├── collections/
├── docs/
├── exports/
├── fragments/
└── cache/
```

## 5. Module Boundaries

Each module owns a specific concern and communicates through contracts or immutable data objects.

Allowed dependency direction:

```text
Support
    ↑
Contracts
    ↑
Schema → Registry → Runtime Engines
                       ├── Permissions
                       ├── Payload
                       ├── Auth
                       └── Analytics
    ↑
Documentation
    ↑
Filament / Commands / HTTP
```

Rules:

- Schema does not depend on Filament, commands, analytics, or AI.
- Registry depends on Schema contracts and cache only.
- Runtime engines depend on Registry contracts and request context.
- Documentation reads from Registry and inspectors.
- AI reads prepared context and writes suggestions only after explicit action.
- Filament is an interface layer, not a domain layer.
- Commands orchestrate services; they do not contain business logic.

## 6. Service Provider

`ApiForgeServiceProvider` is the package composition root.

Responsibilities:

- Merge configuration.
- Register bindings.
- Register singleton services.
- Register commands.
- Register middleware aliases.
- Register event listeners.
- Load package routes.
- Publish configuration.
- Publish migrations.
- Publish prompts.
- Publish schema stubs.
- Publish views.
- Register optional Filament resources when Filament is installed.

It must not contain module logic.

## 7. Contracts Layer

The contracts layer prevents tight coupling and enables plugin replacement.

Core contracts:

```php
interface SchemaLoaderContract
{
    public function supports(string $path): bool;

    public function load(string $path): array;
}

interface SchemaValidatorContract
{
    public function validate(array $schema, ?string $path = null): ValidationResult;
}

interface RegistryContract
{
    public function load(): RegistrySnapshot;

    public function all(): RegistrySnapshot;

    public function find(string $method, string $uri): ?EndpointDescriptor;

    public function reload(): RegistrySnapshot;

    public function invalidate(): void;
}

interface DocumentationGeneratorContract
{
    public function generate(RegistrySnapshot $registry): Documentation;
}

interface DocumentationExporterContract
{
    public function export(Documentation $documentation, ExportOptions $options): ExportResult;
}

interface AIProviderContract
{
    public function complete(AIRequest $request): AIResponse;
}

interface PermissionEngineContract
{
    public function authorize(ApiForgeRequestContext $context): PermissionDecision;
}

interface PayloadEngineContract
{
    public function apply(ApiForgeRequestContext $context, array $payload): PayloadResult;
}

interface AnalyticsDriverContract
{
    public function record(ApiUsageEvent $event): void;

    public function summarize(AnalyticsQuery $query): AnalyticsSummary;
}
```

Contract rules:

- Contracts cannot depend on concrete Laravel models.
- Contracts should accept data objects over raw framework objects where possible.
- Contracts should be stable before `1.0`.
- Breaking contract changes require a major version.

## 8. Core Data Objects

ApiForge should prefer typed immutable data objects for internal boundaries.

Important objects:

- `ApiSchema`
- `EndpointDescriptor`
- `RegistrySnapshot`
- `ValidationResult`
- `Documentation`
- `ExportOptions`
- `ExportResult`
- `AIRequest`
- `AIResponse`
- `ApiForgeRequestContext`
- `PermissionDecision`
- `PayloadResult`
- `SignedApiKey`
- `VerifiedApiKey`
- `ApiUsageEvent`
- `AnalyticsSummary`

These objects should be simple, serializable where useful, and covered by unit tests.

## 9. Schema Layer

Purpose: load, validate, normalize, and compile API definitions from files.

Responsibilities:

- Discover schema files.
- Load PHP array schemas.
- Later support YAML and JSON loaders.
- Validate required schema fields.
- Normalize method, URI, version, auth, roles, scopes, payload, responses, errors, and lifecycle metadata.
- Resolve inheritance.
- Resolve composition and fragments.
- Produce `ApiSchema` objects.

Schema layer components:

```text
Schema/
├── ApiSchema.php
├── SchemaDiscovery.php
├── SchemaManager.php
├── Loaders/
│   ├── PhpSchemaLoader.php
│   ├── JsonSchemaLoader.php
│   └── YamlSchemaLoader.php
├── Validation/
│   ├── SchemaValidator.php
│   └── ValidationResult.php
├── Normalization/
│   └── SchemaNormalizer.php
└── Composition/
    ├── SchemaComposer.php
    └── SchemaInheritanceResolver.php
```

Schema layer must not:

- Query analytics.
- Render documentation.
- Call AI providers.
- Access Filament.
- Perform HTTP requests.

## 10. Registry Layer

The registry is the runtime source for compiled API metadata.

`ApiRegistry` responsibilities:

- Load all valid schemas.
- Compile endpoint descriptors.
- Store and retrieve registry snapshots.
- Expose endpoint lookup APIs.
- Expose indexes for permissions, payload policy, documentation, and testing.
- Invalidate cache when schemas or config change.
- Support hot reload in local environments.

Public methods:

```php
load(): RegistrySnapshot
all(): RegistrySnapshot
find(string $method, string $uri): ?EndpointDescriptor
reload(): RegistrySnapshot
invalidate(): void
```

Endpoint identity:

```text
{version}:{method}:{normalized_uri}
```

Example:

```text
v1:POST:/jobs
```

Registry components:

```text
Registry/
├── ApiRegistry.php
├── RegistryCompiler.php
├── RegistryCache.php
├── RegistrySnapshot.php
├── EndpointDescriptor.php
└── Indexes/
    ├── DocumentationIndex.php
    ├── PermissionIndex.php
    └── PayloadPolicyIndex.php
```

## 11. Documentation Layer

The documentation layer generates human-readable and machine-readable docs from the registry plus Laravel runtime inspection.

Sources:

- Schema files.
- Route definitions.
- Controllers.
- Form requests.
- API resources.
- Examples.
- Error definitions.

Outputs:

- OpenAPI.
- Swagger UI-compatible output.
- Markdown.
- HTML.
- JSON.
- PDF.

Components:

```text
Documentation/
├── DocumentationGenerator.php
├── Documentation.php
├── Inspectors/
│   ├── RouteInspector.php
│   ├── ControllerInspector.php
│   ├── FormRequestInspector.php
│   └── ResourceInspector.php
└── Exporters/
    ├── OpenApiExporter.php
    ├── MarkdownExporter.php
    ├── HtmlExporter.php
    ├── JsonExporter.php
    └── PdfExporter.php
```

Documentation rules:

- Documentation must be generated from the registry, not from duplicate state.
- Exporters must be replaceable.
- HTML and Markdown rendering must sanitize untrusted content.
- OpenAPI is an output format, not the internal source of truth.

## 12. AI Layer

Goal: allow users to connect their own AI provider and generate documentation support material safely.

Supported providers:

- OpenAI.
- Anthropic.
- Gemini.
- OpenRouter.

Provider contract:

```php
interface AIProviderContract
{
    public function complete(AIRequest $request): AIResponse;
}
```

Implementations:

```text
AI/Providers/
├── OpenAIProvider.php
├── AnthropicProvider.php
├── GeminiProvider.php
└── OpenRouterProvider.php
```

AI services:

```text
AI/Services/
├── DocGenerator.php
├── ExampleGenerator.php
├── ErrorExplainer.php
├── TestCaseGenerator.php
└── SDKGenerator.php
```

AI safety rules:

- Redact secrets before prompt creation.
- Treat schemas, docs, examples, and errors as untrusted input.
- Cache AI responses by provider, model, feature, endpoint, and prompt hash.
- Track usage.
- Require explicit write actions before saving AI-generated content.
- Never let AI decide permissions, scopes, validation rules, or key policies.

## 13. API Key Architecture

Purpose: stateless authentication for APIs without database lookup on every request.

Key format:

```text
api_live_{prefix}_{token}
api_test_{prefix}_{token}
```

Claims should include:

- Key id.
- Mode: test or live.
- Scopes.
- Endpoint permissions.
- Rate limit profile.
- Issued timestamp.
- Expiry timestamp.
- Rotation group.

Runtime verification:

1. Parse key.
2. Verify signature.
3. Validate expiry.
4. Check revocation cache.
5. Resolve scopes and permissions.
6. Attach key identity to request context.

Components:

```text
Auth/
├── ApiKeyManager.php
├── ApiKeySigner.php
├── ApiKeyVerifier.php
├── ApiKeyClaims.php
├── SignedApiKey.php
└── RevocationStore.php
```

## 14. Permission Engine

Responsibilities:

- Endpoint permissions.
- Role permissions.
- Scope permissions.
- Field permissions.
- Payload permissions.

Components:

```text
Permissions/
├── PermissionResolver.php
├── PermissionChecker.php
├── PermissionDecision.php
└── PermissionMiddleware.php
```

Decision order:

1. Endpoint exists in registry.
2. Endpoint lifecycle allows runtime access.
3. Authentication requirement is satisfied.
4. API key claims are valid when key auth is used.
5. Role requirements pass.
6. Scope requirements pass.
7. Endpoint permission requirements pass.
8. Field and payload policies pass.

Permission rules:

- Deny by default.
- Ambiguous policy is denied.
- Missing registry entry is denied when strict mode is enabled.
- Permission decisions should include reason codes for analytics and debugging.

## 15. Payload Engine

Responsibilities:

- Hide fields.
- Inject fields.
- Transform fields.
- Override fields.
- Validate fields.
- Mask fields.
- Reject unknown fields when configured.

Components:

```text
Payload/
├── PayloadResolver.php
├── PayloadTransformer.php
├── PayloadValidator.php
├── PayloadPolicy.php
├── PayloadResult.php
└── Transformers/
    ├── TrimTransformer.php
    ├── UppercaseTransformer.php
    └── MaskTransformer.php
```

Payload rules:

- Payload changes must be explicit in schema policy.
- Injected fields should be marked in request context.
- Payload engine must not bypass Laravel model protection.
- Unknown fields should be configurable: allow, strip, or reject.

## 16. Testing Engine

Purpose: provide a registry-aware API testing environment.

Features:

- Request builder.
- Collections.
- Folders.
- Environments.
- Variables.
- Saved requests.
- Request history.
- Import/export.
- Run as role.
- Run as API key.

Components:

```text
Testing/
├── RequestRunner.php
├── CollectionManager.php
├── EnvironmentManager.php
├── VariableResolver.php
├── HistoryStore.php
└── ImportExport/
    ├── CollectionImporter.php
    └── CollectionExporter.php
```

Testing rules:

- Default request target is the current Laravel application.
- External URLs require explicit config.
- Destructive requests should require confirmation in UI.
- Request history must redact secrets.

## 17. Analytics Layer

Analytics is optional and driver-based.

Supported storage:

- Disabled.
- Log.
- Database.
- Redis.
- Custom.

Metrics:

- Request count.
- Endpoint usage.
- Latency.
- Failures.
- Status codes.
- API key activity.
- Permission denials.
- Payload policy violations.

Components:

```text
Analytics/
├── AnalyticsCollector.php
├── MetricsStore.php
├── UsageTracker.php
├── ApiUsageEvent.php
└── Drivers/
    ├── NullAnalyticsDriver.php
    ├── LogAnalyticsDriver.php
    ├── DatabaseAnalyticsDriver.php
    └── RedisAnalyticsDriver.php
```

Analytics rules:

- Analytics must never store full API keys.
- Request bodies are not stored by default.
- IP and user-agent values should be hashable or redacted.
- Analytics driver failures must not break API requests unless strict mode is enabled.

## 18. Middleware Pipeline

Runtime pipeline:

```text
Request
    ↓
Identify ApiForge Endpoint
    ↓
API Key Middleware
    ↓
Permission Middleware
    ↓
Payload Middleware
    ↓
Rate Limiter
    ↓
Controller
    ↓
Response Processor
    ↓
Analytics Logger
```

Middleware classes:

```text
Middleware/
├── IdentifyApiForgeEndpoint.php
├── AuthenticateApiForgeKey.php
├── AuthorizeApiForgeRequest.php
├── ApplyApiForgePayloadPolicy.php
├── ThrottleApiForgeKey.php
├── ProcessApiForgeResponse.php
└── RecordApiForgeUsage.php
```

Default alias:

```php
'apiforge' => [
    IdentifyApiForgeEndpoint::class,
    AuthenticateApiForgeKey::class,
    AuthorizeApiForgeRequest::class,
    ApplyApiForgePayloadPolicy::class,
    ThrottleApiForgeKey::class,
    ProcessApiForgeResponse::class,
    RecordApiForgeUsage::class,
],
```

## 19. Cache Strategy

Redis is preferred for production, but ApiForge must support Laravel cache stores.

Cache namespaces:

```text
apiforge.schemas
apiforge.registry
apiforge.docs
apiforge.permissions
apiforge.payload
apiforge.ai
apiforge.keys.revoked
apiforge.analytics
```

Cache invalidation inputs:

- Schema file hash.
- Schema directory manifest hash.
- Config hash.
- Package version.
- Route cache timestamp.
- Prompt template hash for AI cache.

Cache rules:

- Production should use `apiforge:cache`.
- Local development can use hot reload.
- Cache entries should be scoped by app environment.
- Cache keys must be stable and documented.

## 20. Plugin System

Goal: allow third-party extensions without modifying core modules.

Registration:

```php
ApiForge::plugin(new CustomApiForgePlugin());
```

Plugin contract:

```php
interface ApiForgePlugin
{
    public function register(ApiForgePluginRegistry $registry): void;

    public function boot(ApiForgePluginRegistry $registry): void;
}
```

Plugin types:

- AI providers.
- Documentation exporters.
- Analytics drivers.
- Schema processors.
- Payload transformers.
- Test generators.
- Filament pages.

Plugin rules:

- Plugins register through explicit extension points.
- Plugins should not mutate core singletons after boot.
- Plugin failures should include clear diagnostics.
- Security-sensitive plugins should be disabled by default unless configured.

## 21. Event System

Events:

```text
SchemaDiscovered
SchemaLoaded
SchemaValidated
RegistryCompiled
DocumentationGenerated
ApiKeyCreated
ApiKeyRevoked
ApiCalled
PermissionDenied
PayloadPolicyViolated
AIRequestCompleted
AnalyticsRecorded
```

Event rules:

- Events should carry data objects, not raw secrets.
- Events should not expose full API keys.
- Events should be safe for logging.
- Events should not be required for core control flow.

## 22. Filament Architecture

Filament is an admin interface over core services.

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

Rules:

- Filament pages call services through contracts.
- Filament resources should not become the source of truth for schemas.
- Filament must handle optional modules gracefully.
- Filament should be conditionally registered only when installed and enabled.

## 23. MVP Architecture

Version 1 includes:

- Schema layer.
- Registry layer.
- Documentation layer.
- API key architecture.
- AI documentation services.
- Install, scan, docs, cache, clear, key create, and key revoke commands.
- Basic Filament docs and settings pages.

Version 1 defers:

- Full analytics dashboards.
- Team management.
- SaaS features.
- Marketplace.
- Advanced testing platform.
- Advanced payload governance UI.

## 24. Scalability Path

Future support:

- Multiple projects.
- Team collaboration.
- SaaS deployment.
- Hosted docs portals.
- Enterprise governance.
- Distributed registry cache.
- High-throughput analytics.
- Contract diffing.
- Approval workflows.

The architecture must allow these without rewriting the core registry or schema engine.

## 25. Design Rules

Every module must be:

- Replaceable.
- Testable.
- Cacheable.
- Observable.
- Secure by default.
- Framework-independent where practical.

Forbidden patterns:

- Business logic in service providers.
- Business logic in Filament pages.
- Static helper sprawl.
- Database-first schemas.
- AI-generated security policy.
- Middleware directly parsing schema files.
- Documentation exporters querying raw files instead of the registry.
- Analytics failures breaking requests by default.

## 26. Architecture Acceptance Criteria

The architecture is acceptable when:

- Core modules have clear folders and contracts.
- The schema layer can run without Filament, analytics, AI, or database.
- The registry can compile and cache endpoint metadata.
- Runtime middleware can use the registry without reading files per request.
- Documentation can be generated from the registry.
- API key verification avoids database lookup per request.
- Optional modules can be disabled.
- Plugins have explicit extension points.
- Security-sensitive flows are deterministic and testable.

