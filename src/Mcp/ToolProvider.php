<?php
namespace App\Mcp;
use App\Mcp\Tools\BoampTool;

final class ToolProvider
{
    public function __construct(ToolRegistry $registry,BoampTool $boamp)
    {
        $registry->add(
            'health.ping',
            ['type' => 'object', 'properties' => ['msg' => ['type' => 'string']], 'required' => []],
            fn(array $args) => ['ok' => true, 'echo' => $args['msg'] ?? 'pong']
        );

        $registry->add(
            'boamp.search',
            [
                'type' => 'object',
                'properties' => [
                    'keywords' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'from'     => ['type' => 'string'],
                    'to'       => ['type' => 'string'],
                    'limit'    => ['type' => 'integer', 'minimum' => 1, 'maximum' => 100],
                    'page'     => ['type' => 'integer', 'minimum' => 1],
                ],
                'required' => ['keywords'],
            ],
            fn(array $args) => $boamp->search($args)
        );
    }
}
