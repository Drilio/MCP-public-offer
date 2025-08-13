<?php
namespace App\Controller;

use App\Mcp\JsonRpcHandler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

final class McpController extends AbstractController
{
    public function __construct(private JsonRpcHandler $handler) {}

    #[Route('/mcp', name: 'mcp', methods: ['POST'])]
    public function __invoke(Request $request): JsonResponse
    {
        $payload = json_decode($request->getContent(), true) ?? [];
        $reply   = $this->handler->handle($payload);
        return new JsonResponse($reply);
    }
}
