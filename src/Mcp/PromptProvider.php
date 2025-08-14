<?php
namespace App\Mcp;

final class PromptProvider
{
    public function __construct(PromptRegistry $prompts)
    {
        $prompts->add(
            'team.describe',
            [
                'type' => 'object',
                'properties' => [
                    'teamName' => [
                        'type' => 'string',
                        'description' => 'What is the name of your team?'
                    ],
                    'memberCount' => [
                        'type' => 'integer',
                        'description' => 'How many members are in your team?'
                    ],
                    'languages' => [
                        'type' => 'array',
                        'description' => 'Which programming languages do they use?',
                        'items' => ['type' => 'string']
                    ],
                    'technologies' => [
                        'type' => 'array',
                        'description' => 'List any frameworks, tools, or technologies they use.',
                        'items' => ['type' => 'string']
                    ],
                    'projects' => [
                        'type' => 'array',
                        'description' => 'List notable projects your team has worked on.',
                        'items' => ['type' => 'string']
                    ]
                ],
                'required' => ['teamName', 'memberCount']
            ],
            function (array $args): array {
                $teamName     = isset($args['teamName']) ? trim((string)$args['teamName']) : '';
                $memberCount  = isset($args['memberCount']) ? (int)$args['memberCount'] : null;
                $languages    = isset($args['languages']) ? (array)$args['languages'] : [];
                $technologies = isset($args['technologies']) ? (array)$args['technologies'] : [];
                $projects     = isset($args['projects']) ? (array)$args['projects'] : [];

                $errors = [];
                if ($teamName === '')   { $errors[] = 'Please provide the team name.'; }
                if ($memberCount === null) { $errors[] = 'Please provide the number of team members.'; }

                if ($errors) {
                    return [[ 'type' => 'text', 'text' => "Input error:\n- " . implode("\n- ", $errors) ]];
                }

                // Build the description
                $lines = [];
                $lines[] = "{$teamName} is a team of {$memberCount} member" . ($memberCount === 1 ? '' : 's') . '.';

                if ($languages) {
                    $lines[] = "They use: " . implode(', ', array_map('strval', $languages)) . ".";
                }
                if ($technologies) {
                    $lines[] = "Technologies/frameworks: " . implode(', ', array_map('strval', $technologies)) . ".";
                }
                if ($projects) {
                    $lines[] = "Notable projects: " . implode(', ', array_map('strval', $projects)) . ".";
                }

                return [[ 'type' => 'text', 'text' => implode("\n", $lines) ]];
            },
            'Generate a friendly description of a team from structured inputs.'
        );
    }
}
