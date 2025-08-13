<?php
namespace App\Mcp;

final class ToolRegistry
{
    /** @var array<string,array{schema: array, handler: callable}> */
    private array $tools = [];

    public function add(string $name, array $schema, callable $handler): void
    {
        $this->tools[$name] = ['schema' => $schema, 'handler' => $handler];
    }

    public function list(): array
    {
        $out = [];
        foreach ($this->tools as $name => $def) {
            $out[] = ['name' => $name, 'inputSchema' => $def['schema']];
        }
        return $out;
    }

    public function call(string $name, array $args): mixed
    {
        if (!isset($this->tools[$name])) {
            throw new \InvalidArgumentException("Unknown tool: $name");
        }
        return ($this->tools[$name]['handler'])($args);
    }
}
