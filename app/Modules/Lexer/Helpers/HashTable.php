<?php

namespace App\Modules\Lexer\Helpers;

use App\Modules\Lexer\Models\Token;

class HashTable
{
    protected array $lexTable = [];

    public function __construct()
    {
    }

    public function addToken(Token $lexeme)
    {
        $this->lexTable[] = $lexeme;
    }

    public function toView()
    {
        $array = [];
        foreach ($this->lexTable as $lex)
        {
            $array[] = $lex->toTableView();
        }
        return $array;
    }
}
