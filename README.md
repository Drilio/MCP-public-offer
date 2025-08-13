# MCP Public Offer – Symfony MCP Server

This project is a simple MCP (Model Context Protocol) server built with Symfony.
It uses a ToolRegistry + ToolProvider pattern to register and call tools via JSON-RPC 2.0 over HTTP.

## Project Structure
```
src/
├── Controller/
│ └── McpController.php # Handles /mcp JSON-RPC requests
├── Mcp/
│ ├── JsonRpcHandler.php # JSON-RPC request parser & dispatcher
│ ├── ToolRegistry.php # Stores tool definitions & handlers
│ └── ToolProvider.php # Registers tools into the registry at boot
```

## Installation
Clone the repository
```
git clone https://github.com/your-org/MCP-public-offer.git
cd MCP-public-offer
```
Install dependencies
```
composer install
```
Clear cache (dev mode)
```
php bin/console cache:clear
```
Run the Symfony local server
```
php -S 127.0.0.1:8000 -t public
```
## How Tools Work
All tools are registered in ToolProvider:
```
$registry->add(
'health.ping',
['type' => 'object', 'properties' => ['msg' => ['type' => 'string']], 'required' => []],
fn(array $args) => ['ok' => true, 'echo' => $args['msg'] ?? 'pong']
);
```
ToolRegistry stores the tools and allows listing (tools/list) or calling (tools/call) them.

## API Endpoints
The server listens on /mcp and expects JSON-RPC 2.0 requests.

List Tools
```
curl -s -X POST http://127.0.0.1:8000/mcp \
-H 'Content-Type: application/json' \
-d '{"jsonrpc":"2.0","id":"1","method":"tools/list"}' | jq
```
Call a Tool
```
curl -s -X POST http://127.0.0.1:8000/mcp \
-H 'Content-Type: application/json' \
-d '{"jsonrpc":"2.0","id":"2","method":"tools/call","params":{"name":"health.ping","arguments":{"msg":"hello"}}}' | jq
```

# License

MIT License

