<?php

namespace App\Modules\Lexer\Helpers;

use App\Modules\Lexer\Models\Token;
use RuntimeException;

class Syntaxer
{
    protected string $ans = '';

    protected int $start = 0;

    protected array $variable = [];

    public function __construct(protected HashTable $table)
    {
        $this->parseSyntaxis();
    }

    protected function parseSyntaxis()
    {
        try
        {
            $this->start = 0;
            $this->ans = '';
            $this->parseName();
            $this->parseVariable();
            $this->parseBody();
        }
       catch (\Exception $e)
       {
            dd($e);
            $this->ans = $e->getMessage();
       }
    }

    protected function parseName()
    {
        if ($this->expectToken([
            'title' => 'Ключевое слово program',
            'preg' => 'program',
        ], false, false))
        {
            $this->expectToken(Token::$lexems[5]);
            $this->expectToken([
                'title' => ';',
                'preg' => ';',
            ], false);
        }
    }

    protected function parseVariable()
    {
        $this->parseVar();
    }

    protected function parseVar()
    {
        if ($this->expectToken([
            'title' => 'Ключевое слово var',
            'preg' => 'var',
        ], false, false))
        {
            while ($this->getVariable())
            {
                if (!$this->expectToken([
                    'title' => ',',
                    'preg' => ',',
                ], false, false))
                {
                    if ($this->expectToken([
                        'title' => ': или ,',
                        'preg' => ':',
                    ], false))
                    {
                        $type = $this->expectToken(Token::$lexems[1]);
                        $this->setType($type);
                        $this->expectToken([
                            'title' => ';',
                            'preg' => ';',
                        ], false);
                    }
                }
            }
        }
    }

    protected function getVariable()
    {
        if ($this->table->getToken($this->start)->code === 5)
        {
            $this->variable[$this->table->getToken($this->start)->lexeme] = true;
            $this->start++;
            return $this->table->getToken($this->start)->lexeme;
        }
        return false;
    }

    protected function expectToken(array $lexeme, bool $compareCode = true, bool $throw = true)
    {
        if ($this->table->getSize() === $this->start)
        {
            $text = $lexeme['title'] ?? $lexeme['code'];
            throw new RuntimeException("Ожидался токен {$text}");
        }

        if ($compareCode)
        {
            if ($this->table->getToken($this->start)->code === $lexeme['code'])
            {
                $lex = $this->table->getToken($this->start)->lexeme;
                $this->start++;
                return $lex;
            }
            elseif ($throw)
            {
                $text = $lexeme['title'] ?? $lexeme['code'];
                throw new RuntimeException("Ожидался токен {$text}");
            }
            return false;
        }
        else
        {
            $lex = $this->table->getToken($this->start)->lexeme;
            if ($lex === $lexeme['preg'])
            {
                $this->start++;
                return $lex;
            }
            elseif ($throw)
            {
                throw new RuntimeException("Ожидался токен {$lexeme['title']}");
            }
            return false;
        }
    }

    protected function parseBody()
    {
        if ($this->expectToken([
            'title' => 'Ключевое слово begin',
            'preg' => 'begin',
        ], false, false))
        {
            $this->parseBegin();
            $this->expectToken([
                'title' => '.',
                'preg' => '.',
            ], false);
        }
    }

    protected function setType(string $type)
    {
        foreach ($this->variable as $key => $var)
        {
            if ($var === true)
            {
                $this->variable[$key] = $type;
            }
        }
    }

    protected function parseBegin()
    {
        while (!$this->expectToken([
            'title' => 'Ключевое слово end',
            'preg' => 'end',
        ], false, false))
        {
            $this->switchAction();
        }
    }

    protected function switchAction()
    {
        switch (true)
        {
            case ($this->expectToken(Token::$lexems[5], true, false)):
            {
                $this->parseAssignment();
                break;
            }
            case ($this->expectToken([
                'preg' => 'while',
                'title' => 'Ключевое слово while',
            ], false, false)):
            {
                $this->parseWhile();
                break;
            }
            case ($this->expectToken([
                'preg' => 'if',
                'title' => 'Ключевое слово if',
            ], false, false)):
            {
                $this->parseIf();
                break;
            }
            case ($this->expectToken([
                'preg' => 'writeln',
                'title' => 'Ключевое слово writeln',
            ], false, false)):
            {
                $this->parseWriteLn();
                break;
            }
            default:
            {
                dd($this->start);
            }
        }
    }

    protected function parseWhile()
    {
        $this->expectToken([
            'title' => '(',
            'preg' => '(',
        ], false);

        $this->parseExpression();

        $this->expectToken([
            'title' => ')',
            'preg' => ')',
        ], false);

        $this->expectToken([
            'title' => 'do',
            'preg' => 'do',
        ], false);

        if ($this->expectToken([
            'title' => 'Ключевое слово begin',
            'preg' => 'begin',
        ], false, false))
        {
            $this->parseBegin();
            $this->expectToken([
                'title' => ';',
                'preg' => ';',
            ], false);
        }
        else
        {
            $this->switchAction();
        }
    }

    protected function parseIf()
    {
        $this->expectToken([
            'title' => '(',
            'preg' => '(',
        ], false);

        $this->parseExpression();

        $this->expectToken([
            'title' => ')',
            'preg' => ')',
        ], false);

        $this->expectToken([
            'title' => 'then',
            'preg' => 'then',
        ], false);

        if ($this->expectToken([
            'title' => 'Ключевое слово begin',
            'preg' => 'begin',
        ], false, false))
        {
            $this->parseBegin();
        }
        else
        {
            $this->switchAction();
        }

        while ($this->expectToken([
            'title' => 'Ключевое слово else',
            'preg' => 'else',
        ], false, false))
        {
            if ($this->expectToken([
                'title' => 'Ключевое слово begin',
                'preg' => 'begin',
            ], false, false))
            {
                $this->parseBegin();
            }
            else
            {
                $this->switchAction();
            }
        }

        $this->expectToken([
            'title' => ';',
            'preg' => ';',
        ], false);
    }

    protected function parseWriteLn()
    {
        $this->expectToken([
            'title' => '(',
            'preg' => '(',
        ], false);

        $this->parseExpression();

        $this->expectToken([
            'title' => ')',
            'preg' => ')',
        ], false);

        $this->expectToken([
            'title' => ';',
            'preg' => ';',
        ], false);
    }

    protected function parseAssignment()
    {
        $this->expectToken([
            'title' => 'Оператор :=',
            'preg' => ':=',
        ], false);

        $this->parseExpression();

        $this->expectToken([
            'title' => ';',
            'preg' => ';',
        ], false);
    }

    protected function parseExpression()
    {
        while ($this->expectToken(Token::$lexems[5], true, false) || $this->expectToken(Token::$lexems[4], true, false))
        {
            if (!$this->expectToken(Token::$lexems[2], true, false))
            {
                break;
            }
        }
    }

    public function formAns()
    {
        return 'test';
    }
}
