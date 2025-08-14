<?php
namespace App\Mcp\Tools;

use Symfony\Contracts\HttpClient\HttpClientInterface;

final class BoampTool
{
    private const API_URL = 'https://boamp-datadila.opendatasoft.com/api/explore/v2.1/catalog/datasets/boamp/records';

    public function __construct(private HttpClientInterface $http) {}

    /**
     * @param array{
     *   keywords: string|array<string>,
     *   from?: string,
     *   to?: string,
     *   limit?: int,
     *   page?: int
     * } $args
     */
    public function search(array $args): array
    {
        // normalize keywords -> single space-separated string
        $kw = $args['keywords'] ?? '';
        if (is_array($kw)) {
            $kw = implode(' ', array_filter(array_map('trim', $kw)));
        } else {
            $kw = trim((string)$kw);
        }
        if ($kw === '') {
            return ['isError' => true, 'message' => 'keywords are required'];
        }

        // pagination
        $limit  = max(1, min((int)($args['limit'] ?? 10), 100));
        $page   = max(1, (int)($args['page'] ?? 1));
        $offset = ($page - 1) * $limit;

        // WHERE: full-text search + optional date range
        // Full-text must be in ODSQL: search("..."), then AND date filters
        $terms = str_replace('"', '\"', $kw); // simple quote escape
        $whereParts = ['search("'.$terms.'")'];
        if (!empty($args['from'])) {
            $whereParts[] = "dateparution >= date'".$this->esc($args['from'])."'";
        }
        if (!empty($args['to'])) {
            $whereParts[] = "dateparution <= date'".$this->esc($args['to'])."'";
        }
        $where = implode(' AND ', $whereParts);

        // v2.1 param names are snake_case
        $query = [
            'where'    => $where,
            'limit'    => $limit,
            'offset'   => $offset,
            'order_by' => 'dateparution DESC',
        ];

        $resp = $this->http->request('GET', self::API_URL, ['query' => $query]);
        $status = $resp->getStatusCode();
        if ($status !== 200) {
            return ['isError' => true, 'message' => "BOAMP API returned HTTP $status", 'debug_query' => $query];
        }
        $json = $resp->toArray(false);

        $items = [];
        foreach (($json['results'] ?? []) as $r) {
            $items[] = [
                'id'    => $r['id'] ?? null,
                'title' => $r['objet'] ?? ($r['intitule'] ?? null),
                'date'  => $r['dateparution'] ?? null,
                'raw'   => $r,
            ];
        }

        return [
            'ok'          => true,
            'total'       => $json['total_count'] ?? count($items),
            'page'        => $page,
            'page_size'   => $limit,
            'keywords'    => $kw,
            'items'       => $items,
            'debug_query' => $query,
        ];
    }

    private function esc(string $v): string
    {
        // very small sanitizer for ODSQL literals (dates here)
        return preg_replace('/[^0-9T:\\-]/', '', $v) ?? '';
    }
}
