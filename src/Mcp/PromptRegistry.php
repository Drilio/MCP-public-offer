<?php
namespace App\Mcp;

final class PromptRegistry
{
    /** @var array<string,array{description?:string, inputSchema:array, handler: callable}> */
    private array $prompts = [];

    public function add(string $name, array $inputSchema, callable $handler, string $description = ''): void
    {
        $this->prompts[$name] = [
            'description' => $description,
            'inputSchema' => $inputSchema,
            'handler'     => $handler,
        ];
    }

    public function list(): array
    {
        $out = [];
        foreach ($this->prompts as $name => $p) {
            $schema = $p['inputSchema'];
            $out[] = [
                'name'         => $name,
                'description'  => $p['description'] ?? '',
                'inputSchema'  => $schema,
                'input_schema' => $schema,
            ];
        }
        return $out;
    }

    public function call(string $name, array $args): array
    {
        if (!isset($this->prompts[$name])) {
            throw new \InvalidArgumentException("Unknown prompt: $name");
        }
        return ($this->prompts[$name]['handler'])($args);
    }
}
