<?php

namespace App\Livewire;

use Livewire\Component;
use App\Modules\Lexer\Helpers\HashTable;
use App\Modules\Lexer\Helpers\Lexer;
use App\Modules\Lexer\Models\Token;
use RuntimeException;

class Compiler extends Component
{
    protected array $variable = [];

    protected Lexer $lexer;

    public string $text = "program my_compiler;
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
    end.";

    public string $ans = '';

    public int $start = 0;

    public function render()
    {
        $matches = [];
        $start = 20;
        preg_match('~^[\p{Zs}\t\n]*' . Token::$lexems[1]['preg'] . '~', substr($this->text, $start), $matches, PREG_OFFSET_CAPTURE);
        //dd($matches, '~^[\p{Zs}\t\n]*' . Token::$lexems[1]['preg'] . '~', substr($this->text, $start));//, $start + strlen($matches[0][0]));
        return view('livewire.compiler');
    }

    public function parseLexems()
    {
        $this->lexer = new Lexer($this->text);

        $this->ans = $this->lexer->formAns();
    }

    public function parseSyntaxis()
    {
        try
        {
            $this->start = 0;
            $this->ans = '';
            $this->parseName($this->text);
            $this->parseVariable($this->text);
            $this->parseBody($this->text);
        }
       catch (\Exception $e)
       {
            $this->ans = $e->getMessage();
       }
    }

    public function parseName(string $text)
    {
        if ($this->expectToken([
            'title' => 'Ключевое слово program',
            'preg' => 'program',
        ], $text, false))
        {
            $this->expectToken(Token::$lexems[5], $text);
            $this->expectToken([
                'title' => ';',
                'preg' => ';',
            ], $text);
        }
    }

    public function parseVariable(string $text)
    {
        $this->parseVar($text);
        dump($this->variable);
    }

    public function parseVar(string $text)
    {
        if ($this->expectToken([
            'title' => 'Ключевое слово var',
            'preg' => 'var',
        ], $text, false))
        {
            while ($this->getVariable($text))
            {
                if (!$this->expectToken([
                    'title' => ',',
                    'preg' => ',',
                ], $text, false))
                {
                    if ($this->expectToken([
                        'title' => ': или ,',
                        'preg' => ':',
                    ], $text))
                    {
                        $this->expectToken(Token::$lexems[1], $text);
                        $this->expectToken([
                            'title' => ';',
                            'preg' => ';',
                        ], $text);
                    }
                }
            }
        }
    }

    public function getVariable(string $text)
    {
        $matches = [];
        preg_match('~^[\p{Zs}\t\n]*' . Token::$lexems[1]['preg'] . '~', substr($text, $this->start), $matches, PREG_OFFSET_CAPTURE);
        if (isset($matches[0]))
        {
            return false;
        }

        preg_match('~^[\p{Zs}\t\n]*' . Token::$lexems[5]['preg'] . '~', substr($text, $this->start), $matches, PREG_OFFSET_CAPTURE);
        if (isset($matches[1]))
        {
            $this->variable[$matches[1][0]] = true;
            $this->start += $matches[1][1] + strlen($matches[1][0]);
            return $matches[1][0];
        }
        elseif (isset($matches[0]))
        {
            $this->variable[$matches[0][0]] = true;
            $this->start += $matches[0][1] + strlen($matches[0][0]);
            return $matches[0][0];
        }
        else
        {
            return false;
        }
    }

    public function expectToken(array $lexeme, string $text, bool $throw = true)
    {
        $matches = [];
        preg_match('~^[\p{Zs}\t\n]*' . $lexeme['preg'] . '~', substr($text, $this->start), $matches, PREG_OFFSET_CAPTURE);
        if (isset($matches[1]))
        {
            $this->start += $matches[1][1] + strlen($matches[1][0]);
            return $matches[1][0];
        }
        elseif (isset($matches[0]))
        {
            $this->start += $matches[0][1] + strlen($matches[0][0]);
            return $matches[0][0];
        }
        else
        {
            if ($throw)
            {
                throw new RuntimeException("Ожидался токен {$lexeme['title']}");
            }
            return false;
        }
    }

    public function parseBody(string $text)
    {
        if ($this->expectToken([
            'title' => 'Ключевое слово begin',
            'preg' => 'begin',
        ], $text, false))
        {
            dump('begin');
        }
    }
}
