<?php

namespace App\Modules\Lexer\Controller;

use App\Modules\Lexer\Helpers\HashTable;
use App\Modules\Lexer\Models\Lexeme;
use Exception;

class LexerController
{
    public HashTable $table;

    public function index()
    {
        $this->table = new HashTable();
        $text = "
        program my_compiler;
        var
        a, b, c: integer;
        name: string;
        begin
        a := 1;
        b := 5;
        c := 0;
        while (a < b) do
        begin
        c := c * 10 + a;
        end;
        if (c = 0) then
        begin
        name := 'Buddy!';
        end;
        if (b > c) then
        begin
        name := 'Guy!';
        end
        else
        begin
        name := 'World!';
        end;
        message := 'Hello, ' + name;
        writeln(message);
        end.
        ";
        dump('Текст программы:', $text);
        $text = $this->prelexer($text);
        $lexems = explode(' ', $text);
        $this->parseLexems($lexems);
        dd($this->table->toView());
        dd('test 2');
    }

    public function prelexer(string $text)
    {
        $text = str_replace("\n", '', $text);
        while (str_contains($text, "  "))
        {
            $text = str_replace("  ", ' ', $text);
        }
        return $text;
    }

    public function parseLexems(array $lexems)
    {
        $isStart = true;
        $isTitle = false;
        $isVar = false;
        $isType = false;
        $variable = [];

        foreach ($lexems as $lexem)
        {
            if ($lexem === '')
            {
                continue;
            }

            if ($isStart)
            {
                if (strtolower($lexem) !== 'program')
                {
                    trigger_error("Программа должна начинаться с ключевого слова PROGRAM!");
                }
                else
                {
                    $this->table->addLexem(new Lexeme($lexem, 1));
                    $isStart = false;
                    $isTitle = true;
                    continue;
                }
            }

            if (in_array($lexem, Lexeme::$OPERATORS))
            {
                $this->table->addLexem(new Lexeme($lexem, 3));
                continue;
            }

            if (in_array($lexem, Lexeme::$SEPARATOR))
            {
                $this->table->addLexem(new Lexeme($lexem, 6));
                continue;
            }

            if (in_array($lexem, Lexeme::$KEYWORDS))
            {
                $this->table->addLexem(new Lexeme($lexem, 1));
                continue;
            }

            if ($isTitle)
            {
                $this->table->addLexem(new Lexeme(str_replace(';', '', $lexem), 2));
                if (str_ends_with($lexem, ';'))
                {
                    $this->table->addLexem(new Lexeme(';', 6));
                }
                $isTitle = false;
                continue;
            }

            if ($lexem === 'var')
            {
                $this->table->addLexem(new Lexeme($lexem, 1));
                $isVar = true;
                continue;
            }

            if ($isVar)
            {
                if ($lexem === 'begin')
                {
                    $this->table->addLexem($lexem, 1);
                    $isVar = false;
                    continue;
                }

                if (str_ends_with($lexem, ':'))
                {
                    if ($lexem !== ':')
                    {
                        $var = str_replace(':', '', $lexem);
                        if (str_ends_with($lexem, ':'))
                        {
                            $this->table->addLexem(new Lexeme(';', 6));
                        }
                        $variable[] = $var;
                        $this->table->addLexem(new Lexeme($var, 4));
                    }
                    $isVar = false;
                    $isType = true;
                    continue;
                }

                if (str_ends_with($lexem, ','))
                {
                    if ($lexem !== ',')
                    {
                        $var = str_replace(',', '', $lexem);
                        if (str_ends_with($lexem, ':'))
                        {
                            $this->table->addLexem(new Lexeme(',', 6));
                        }
                        $variable[] = $var;
                        $this->table->addLexem(new Lexeme(str_replace(',', '', $lexem), 4));
                    }
                    continue;
                }
                $variable[] = $lexem;
                $this->table->addLexem(new Lexeme($lexem, 4));
                continue;
            }

            if ($isType)
            {
                if (str_ends_with($lexem, ';'))
                {
                    if ($lexem !== ';')
                    {
                        $this->table->addLexem(new Lexeme(str_replace(';', '', $lexem), 5));
                        $this->table->addLexem(new Lexeme(';', 6));
                    }
                    $isVar = true;
                    $isType = false;
                    continue;
                }
                $this->table->addLexem(new Lexeme($lexem, 5));
                continue;
            }

            if ($lexem === ':=')
            {
                $this->table->addLexem(new Lexeme($lexem, 3));
                continue;
            }

            dump($lexem);
        }
    }
}
