<?php

namespace ApiForge\Middleware;

use ApiForge\Contracts\ApiRegistryContract;
use ApiForge\Contracts\SignatureValidatorContract;
use ApiForge\Exceptions\InvalidApiKeyException;
use ApiForge\Support\EndpointDescriptor;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyMiddleware
{
    public function __construct(
        protected SignatureValidatorContract $validator,
        protected ApiRegistryContract $registry,
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $endpoint = $this->resolveEndpoint($request);

        if ($endpoint === null || ! $endpoint->auth) {
            return $next($request);
        }

        $header = (string) config('apiforge.keys.header', 'X-Api-Key');
        $apiKey = $request->header($header);

        if (! is_string($apiKey) || $apiKey === '') {
            return response()->json(['message' => 'API key required.'], 401);
        }

        try {
            $verified = $this->validator->verify($apiKey);
        } catch (InvalidApiKeyException $exception) {
            return response()->json(['message' => $exception->getMessage()], 401);
        }

        if ($endpoint->scopes !== [] && ! $this->scopesMatch($endpoint, $verified->scopes)) {
            return response()->json(['message' => 'Insufficient API key scopes.'], 403);
        }

        $permission = $endpoint->method.':'.$endpoint->endpoint;

        if ($verified->permissions !== [] && ! $verified->hasPermission($permission) && ! $verified->hasPermission('*')) {
            return response()->json(['message' => 'API key lacks endpoint permission.'], 403);
        }

        $request->attributes->set('apiforge.api_key', $verified);
        $request->attributes->set('apiforge.endpoint', $endpoint);

        return $next($request);
    }

    protected function resolveEndpoint(Request $request): ?EndpointDescriptor
    {
        return $this->registry->find(
            $request->method(),
            '/'.ltrim($request->path(), '/')
        );
    }

    /**
     * @param  array<int, string>  $keyScopes
     */
    protected function scopesMatch(EndpointDescriptor $endpoint, array $keyScopes): bool
    {
        if (in_array('*', $keyScopes, true)) {
            return true;
        }

        foreach ($endpoint->scopes as $requiredScope) {
            if (! in_array($requiredScope, $keyScopes, true)) {
                return false;
            }
        }

        return true;
    }
}
