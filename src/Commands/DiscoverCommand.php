<?php

namespace ZeeshanMushtaq\ApiNexa\Commands;

use Illuminate\Console\Command;
use Illuminate\Routing\Route;
use Illuminate\Support\Facades\Route as RouteFacade;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Finder\Finder;

class DiscoverCommand extends Command
{
    protected $signature = 'apinexa:discover {--routes=api : Filter routes by middleware prefix} {--output-dir=api-nexa/schemas : Directory to save schema files}';

    protected $description = 'Auto-discover API routes and generate schema files from controllers and form requests';

    public function handle(): int
    {
        $routePrefix = $this->option('routes');
        $outputDir = base_path($this->option('output-dir'));

        if (!is_dir($outputDir)) {
            mkdir($outputDir, 0755, true);
        }

        $routes = collect(RouteFacade::getRoutes())
            ->filter(function (Route $route) use ($routePrefix) {
                $middleware = $route->middleware();
                return in_array($routePrefix, $middleware) || $routePrefix === '*';
            })
            ->groupBy(function (Route $route) {
                return $this->getControllerName($route);
            });

        if ($routes->isEmpty()) {
            $this->components->warn("No routes found with middleware '{$routePrefix}'.");
            return self::FAILURE;
        }

        $generatedCount = 0;

        foreach ($routes as $controller => $routeGroup) {
            if (!$controller) {
                continue;
            }

            $schemaFile = $this->generateSchemaFile($controller, $routeGroup, $outputDir);
            if ($schemaFile) {
                $this->line("  ✓ Generated {$controller}");
                $generatedCount++;
            }
        }

        $this->components->info("Generated {$generatedCount} schema file(s) in {$outputDir}");
        $this->line('');

        if ($this->confirm('Run apinexa:scan to compile schemas?', true)) {
            return $this->call('apinexa:scan', ['--force' => true]);
        }

        return self::SUCCESS;
    }

    protected function getControllerName(Route $route): ?string
    {
        $action = $route->getAction();
        if (isset($action['controller'])) {
            $controller = $action['controller'];
            if (is_string($controller)) {
                return explode('@', $controller)[0];
            }
        }
        return null;
    }

    protected function generateSchemaFile(string $controller, $routeGroup, string $outputDir): bool
    {
        try {
            $controllerClass = new ReflectionClass($controller);
            $endpoints = [];

            foreach ($routeGroup as $route) {
                $endpoint = $this->extractEndpointData($route, $controllerClass);
                if ($endpoint) {
                    $endpoints[] = $endpoint;
                }
            }

            if (empty($endpoints)) {
                return false;
            }

            $controllerBaseName = class_basename($controller);
            $fileName = strtolower(str_replace('Controller', '', $controllerBaseName)) . '.php';
            $schemaPath = "{$outputDir}/{$fileName}";

            $content = $this->generateSchemaContent($controllerBaseName, $endpoints);
            file_put_contents($schemaPath, $content);

            return true;
        } catch (\Exception $e) {
            $this->components->error("Failed to generate schema for {$controller}: {$e->getMessage()}");
            return false;
        }
    }

    protected function extractEndpointData(Route $route, ReflectionClass $controllerClass): ?array
    {
        $action = $route->getAction();
        $controller = $action['controller'] ?? null;

        if (!$controller || !is_string($controller)) {
            return null;
        }

        [$controllerName, $methodName] = explode('@', $controller);

        if (!$controllerClass->hasMethod($methodName)) {
            return null;
        }

        $method = $controllerClass->getMethod($methodName);
        $parameters = $this->extractParameters($method);

        return [
            'method' => strtoupper(implode(',', $route->methods)[0] ?? 'GET'),
            'endpoint' => $route->uri,
            'name' => $methodName,
            'description' => "Auto-discovered from {$controllerName}@{$methodName}",
            'parameters' => $parameters,
            'version' => 'v1',
        ];
    }

    protected function extractParameters(ReflectionMethod $method): array
    {
        $parameters = [];

        foreach ($method->getParameters() as $param) {
            $type = $param->getType();
            $formRequestClass = null;

            // Check if parameter is a Form Request
            if ($type && !$type->isBuiltin()) {
                $paramClass = $type->getName();
                if (class_exists($paramClass) && is_subclass_of($paramClass, 'Illuminate\Foundation\Http\FormRequest')) {
                    $formRequestClass = $paramClass;
                }
            }

            if ($formRequestClass) {
                $rules = $this->extractFormRequestRules($formRequestClass);
                foreach ($rules as $fieldName => $fieldRules) {
                    $parameters[] = [
                        'name' => $fieldName,
                        'in' => 'body',
                        'required' => in_array('required', (array) $fieldRules),
                        'type' => $this->inferTypeFromRules((array) $fieldRules),
                        'description' => "From {$formRequestClass}",
                    ];
                }
            }
        }

        return $parameters;
    }

    protected function extractFormRequestRules(string $formRequestClass): array
    {
        try {
            $instance = app($formRequestClass);
            $rules = $instance->rules();
            return is_array($rules) ? $rules : [];
        } catch (\Exception $e) {
            return [];
        }
    }

    protected function inferTypeFromRules(array $rules): string
    {
        $ruleString = implode('|', $rules);

        if (str_contains($ruleString, 'integer')) return 'integer';
        if (str_contains($ruleString, 'numeric')) return 'number';
        if (str_contains($ruleString, 'boolean')) return 'boolean';
        if (str_contains($ruleString, 'array')) return 'array';
        if (str_contains($ruleString, 'email')) return 'string';
        if (str_contains($ruleString, 'date')) return 'string';

        return 'string';
    }

    protected function generateSchemaContent(string $controllerName, array $endpoints): string
    {
        $endpointsPhp = var_export($endpoints, true);

        return <<<'PHP'
<?php

return [
    'name' => '%s API',
    'description' => 'Auto-discovered from %s controller',
    'endpoints' => %s,
];
PHP
        . "\n"
        . sprintf("return [\n    'name' => '%s API',\n    'description' => 'Auto-discovered from %s controller',\n    'endpoints' => %s,\n];\n",
            str_replace('Controller', '', $controllerName),
            $controllerName,
            $endpointsPhp
        );
    }
}
