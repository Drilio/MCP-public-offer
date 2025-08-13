<?php
namespace App\Mcp;

final class JsonRpcHandler
{
    public function __construct(private ToolRegistry $registry) {
        $this->registry->add(
            'health.ping',
            ['type' => 'object', 'properties' => ['msg' => ['type' => 'string']], 'required' => []],
            fn(array $args) => ['ok' => true, 'echo' => $args['msg'] ?? 'pong']
        );
    }

    public function handle(array $msg): array
    {
        $id = $msg['id'] ?? null;
        try {
            $method = $msg['method'] ?? '';
            if ($method === 'tools/list') {
                return ['jsonrpc' => '2.0', 'id' => $id, 'result' => ['tools' => $this->registry->list()]];
            }
            if ($method === 'tools/call') {
                $params = $msg['params'] ?? [];
                $name = $params['name'] ?? '';
                $args = $params['arguments'] ?? [];
                $result = $this->registry->call($name, $args);
                return ['jsonrpc' => '2.0', 'id' => $id, 'result' => ['content' => $result]];
            }
            return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => -32601, 'message' => 'Method not found']];
        } catch (\Throwable $e) {
            return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => -32000, 'message' => $e->getMessage()]];
        }
    }
}
