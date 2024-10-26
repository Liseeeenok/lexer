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

    public function addNode(Node &$node)
    {
        $this->lists[] = $node;
    }

    public function formAns(int $level)
    {
        $indent = str_repeat(" ", $level);
        $ans = $indent . "|-Узел: " . $this->name . "\n";

        foreach ($this->lists as $list)
        {
            $ans .=  $list->formAns($level + 1);
        }

        return $ans;
    }
}
