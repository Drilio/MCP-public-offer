<?php
namespace App\Mcp;

final class JsonRpcHandler
{
    public function __construct(
        private readonly ToolRegistry   $registry,
        ToolProvider                    $provider,
        private readonly PromptRegistry $prompts,
        PromptProvider                  $promptProvider
    ) {}

    public function handle(array $msg): array
    {
        $id = $msg['id'] ?? null;

        try {
            $method = $msg['method'] ?? '';

            if ($method === 'initialize') {
                return [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'protocolVersion' => '2025-06-18',
                        'serverInfo' => [
                            'name' => 'mcp-public-offer-symfony',
                            'version' => '0.1.0',
                        ],
                        'capabilities' => [
                            'tools' => new \stdClass(),
                            'prompts' => new \stdClass(),
                        ],
                    ],
                ];
            }

            if ($method === 'tools/list') {
                return [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => ['tools' => $this->registry->list()],
                ];
            }

            if ($method === 'tools/call') {
                $params = $msg['params'] ?? [];
                $name   = $params['name'] ?? '';
                $args   = $params['arguments'] ?? [];

                $result = $this->registry->call($name, $args);

                return [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'content' => [
                            [
                                'type' => 'text',
                                'text' => json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE),
                            ],
                        ],
                    ],
                ];
            }

            if ($method === 'prompts/list') {
                return ['jsonrpc' => '2.0', 'id' => $id, 'result' => ['prompts' => $this->prompts->list()]];
            }

            if ($method === 'prompts/call') {
                $params = $msg['params'] ?? [];
                $name   = $params['name'] ?? '';
                $args   = $params['arguments'] ?? [];

                $parts  = $this->prompts->call($name, $args);

                return ['jsonrpc' => '2.0', 'id' => $id, 'result' => ['content' => $parts]];
            }
            if ($method === 'prompts/generate' || $method === 'prompts/get') {
                $params = $msg['params'] ?? [];
                $name   = $params['name'] ?? '';
                $args   = $params['arguments'] ?? [];

                $parts = $this->prompts->call($name, $args);

                $single = null;

                if (is_array($parts) && count($parts) > 0) {
                    if (count($parts) === 1) {
                        $single = $parts[0];
                    } else {
                        $chunks = [];
                        foreach ($parts as $p) {
                            if (is_array($p) && ($p['type'] ?? '') === 'text' && isset($p['text'])) {
                                $chunks[] = (string)$p['text'];
                            } else {
                                $chunks[] = json_encode($p, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
                            }
                        }
                        $single = ['type' => 'text', 'text' => implode("\n\n", $chunks)];
                    }
                } else {
                    $single = ['type' => 'text', 'text' => ''];
                }

                return [
                    'jsonrpc' => '2.0',
                    'id' => $id,
                    'result' => [
                        'messages' => [
                            [
                                'role'    => 'assistant',
                                'content' => $single,
                            ],
                        ],
                    ],
                ];
            }
            return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => -32601, 'message' => 'Method not found']];
        } catch (\Throwable $e) {
            return ['jsonrpc' => '2.0', 'id' => $id, 'error' => ['code' => -32000, 'message' => $e->getMessage()]];
        }
    }
}
