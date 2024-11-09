<?php

namespace App\Modules\Lexer\Helpers;

use App\Modules\Lexer\Models\Node;

class Compiler
{
    public function __construct(public Node $mainNode)
    {
    }

    public function formPsevdocode()
    {
        return $this->mainNode->formPsevdocode(0);
    }
}
