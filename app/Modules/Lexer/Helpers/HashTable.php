<?php

namespace App\Modules\Lexer\Helpers;

use App\Modules\Lexer\Models\Lexeme;

class HashTable
{
    protected array $lexTable = [];

    public function addLexem(Lexeme $lexeme)
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
