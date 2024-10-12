<?php

namespace App\Modules\Lexer\Helpers;

use App\Modules\Lexer\Models\Token;

class Lexer
{
    protected HashTable $table;

    public function __construct(string $text)
    {
        $this->parseLexems($text);
    }

    public function getTable()
    {
        return $this->table;
    }

    protected function parseLexems(string $text)
    {
        $table = new HashTable();
        $count = 0;
        $start = 0;
        do
        {
            $count++;

            $lexem = null;

            $matches = [];
            foreach (Token::$lexems as $code => $type)
            {
                preg_match('~^[\p{Zs}\t\n]*' . $type['preg'] . '~', substr($text, $start), $matches, PREG_OFFSET_CAPTURE);
                if (isset($matches[1]))
                {
                    $lexem = new Token($matches[1][0], $code, $matches[1][1]);
                    $table->addToken($lexem);
                    $start += $matches[1][1] + strlen($lexem->lexeme);
                    break;
                }
                elseif (isset($matches[0]))
                {
                    $lexem = new Token($matches[0][0], $code, $matches[0][1]);
                    $table->addToken($lexem);
                    $start += $matches[0][1] + strlen($lexem->lexeme);
                    break;
                }
            }
        } while ($lexem && $count < 100);

        $this->table = $table;
    }

    public function formAns()
    {
        $ans = '';

        foreach ($this->table->toView() as $key => $lexeme)
        {
            $ans .= $key . '. ' . $lexeme['type'] . ' - ' . $lexeme['lexeme'] . "\n";
        }

        return $ans;
    }
}
