<?php

declare(strict_types=1);

namespace Takamichi\PhpstanTodoRules;

use PhpParser\Node;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

class TodoCommentRule implements Rule
{
    /** @var array<string, bool> */
    private array $processed = [];

    public function getNodeType(): string
    {
        // コメントのNodeを指定しても取得できないので、すべてのNodeを取得するように指定。
        return Node::class;
    }

    public function processNode(Node $node, Scope $scope): array
    {
        $errors = [];
        var_dump($node::class); // TODO: デバッグ用、削除

        foreach ($node->getComments() as $comment) {
            // コメントがすでに処理済みではないかチェックし、余計な再処理を防ぐ。
            $hash = spl_object_hash($comment);
            if (($this->processed[$hash] ?? false) === true) {
                continue;
            }

            if (is_string($text = $this->getTodoComment($comment->getReformattedText()))) {
                $this->processed[$hash] = true;
                $errors[] = sprintf('Unresolved TODO comment: "%s"', $text);
            }
        }

        return $errors;
    }

    // FIXME: 1つのコメントNode内に複数のTODOが含まれている場合に対応する
    private function getTodoComment(string $text): ?string
    {
        if (preg_match('/\bTODO\b:?\s*(?<content>.+?)((\s*\*+\/)|(\s+))?$/im', $text, $matches) >= 1) {
            return trim($matches['content'] ?? '');
        }

        return null;
    }
}
