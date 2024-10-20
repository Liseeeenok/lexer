<?php

namespace App\Modules\Lexer\Models;

class Node
{
    protected array $lists = [];

    protected int $level = 0;

    public function __construct(protected string $name = '')
    {
    }

    public function addToken(?Token $token)
    {
        if ($token)
        {
            $this->lists[] = $token;
        }
    }

    public function addNode(Node $node)
    {
        $node->setLevel($this->level + 1);
        $this->lists[] = $node;
    }

    public function setLevel(int $level)
    {
        $this->level = $level;
    }
}
