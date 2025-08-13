<?php
namespace App\Mcp;

final class ToolProvider
{
    public function __construct(ToolRegistry $registry)
    {
        $registry->add(
            'health.ping',
            ['type' => 'object', 'properties' => ['msg' => ['type' => 'string']], 'required' => []],
            fn(array $args) => ['ok' => true, 'echo' => $args['msg'] ?? 'pong']
        );

    }
}
