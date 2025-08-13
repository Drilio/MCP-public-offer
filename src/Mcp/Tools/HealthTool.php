<?php
namespace App\Mcp\Tools;

final class HealthTool
{
    public function ping(array $args): array
    {
        return ['ok' => true, 'echo' => $args['msg'] ?? 'pong'];
    }
}
