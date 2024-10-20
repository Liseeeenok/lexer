<?php

namespace App\Modules\Lexer\Helpers;

use App\Modules\Lexer\Models\Node;
use App\Modules\Lexer\Models\Token;
use RuntimeException;

class Syntaxer
{
    protected string $ans = '';

    protected int $start = 0;

    protected array $variable = [];

    protected Node $mainNode;

    protected Node $actualNode;

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
            $node = new Node('main');
            $this->mainNode = $node;
            $this->actualNode = $this->mainNode;
            $this->parseName();
            $this->parseVariable();
            $this->parseBody();
            dd($this->mainNode);
        }
       catch (\Exception $e)
       {
            dd($e);
            $this->ans = $e->getMessage();
       }
    }

    protected function parseName()
    {
        $node = $this->actualNode;
        $this->actualNode = new Node('name programm');
        if ($token = $this->expectToken([
            'title' => 'Ключевое слово program',
            'preg' => 'program',
        ], false, false))
        {
            $this->actualNode->addToken($token);
            $this->actualNode->addToken($this->expectToken(Token::$lexems[5]));
            $this->actualNode->addToken($this->expectToken([
                'title' => ';',
                'preg' => ';',
            ], false));
            $node->addNode($this->actualNode);
            $this->actualNode = $node;
        }
    }

    protected function parseVariable()
    {
        $this->parseVar();
    }

    protected function parseVar()
    {
        $node = $this->actualNode;
        $this->actualNode = new Node('Объявление переменных');
        if ($token = $this->expectToken([
            'title' => 'Ключевое слово var',
            'preg' => 'var',
        ], false, false))
        {
            $this->actualNode->addToken($token);
            while ($token = $this->getVariable())
            {
                $this->actualNode->addToken($token);
                $token = $this->expectToken([
                    'title' => ',',
                    'preg' => ',',
                ], false, false);
                if (!$token)
                {
                    if ($token = $this->expectToken([
                        'title' => ': или ,',
                        'preg' => ':',
                    ], false))
                    {
                        $this->actualNode->addToken($token);
                        $token = $this->expectToken(Token::$lexems[1]);
                        $type = $token->lexeme;
                        $this->setType($type);
                        $this->actualNode->addToken($token);
                        $this->actualNode->addToken($this->expectToken([
                            'title' => ';',
                            'preg' => ';',
                        ], false));
                    }
                }
                else
                {
                    $this->actualNode->addToken($token);
                }
            }
            $node->addNode($this->actualNode);
            $this->actualNode = $node;
        }
    }

    protected function getVariable()
    {
        $token = $this->table->getToken($this->start);
        if ($token->code === 5)
        {
            $this->variable[$token->lexeme] = true;
            $this->start++;
            return $token;
        }
        return false;
    }

    protected function expectToken(array $lexeme, bool $compareCode = true, bool $throw = true) : null|Token|bool
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
                $token = $this->table->getToken($this->start);
                $this->start++;
                return $token;
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
            $token = $this->table->getToken($this->start);
            if ($token->lexeme === $lexeme['preg'])
            {
                $this->start++;
                return $token;
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
        $node = $this->actualNode;
        $this->actualNode = new Node('Тело программы');
        if ($token = $this->expectToken([
            'title' => 'Ключевое слово begin',
            'preg' => 'begin',
        ], false, false))
        {
            $this->actualNode->addToken($token);
            $this->parseBegin();
            $this->actualNode->addToken($this->expectToken([
                'title' => '.',
                'preg' => '.',
            ], false));
            $node->addNode($this->actualNode);
            $this->actualNode = $node;
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
        while (!($token = $this->expectToken([
            'title' => 'Ключевое слово end',
            'preg' => 'end',
        ], false, false)))
        {
            $this->switchAction();
        }
        $this->actualNode->addToken($token);
    }

    protected function switchAction()
    {
        switch (true)
        {
            case ($token = $this->expectToken(Token::$lexems[5], true, false)):
            {
                $node = $this->actualNode;
                $this->actualNode = new Node('Присваивание переменной');
                $this->actualNode->addToken($token);
                $this->parseAssignment();
                $node->addNode($this->actualNode);
                $this->actualNode = $node;
                break;
            }
            case ($token = $this->expectToken([
                'preg' => 'while',
                'title' => 'Ключевое слово while',
            ], false, false)):
            {
                $node = $this->actualNode;
                $this->actualNode = new Node('Цикл while');
                $this->actualNode->addToken($token);
                $this->parseWhile();
                $node->addNode($this->actualNode);
                $this->actualNode = $node;
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
        $this->actualNode->addToken($this->expectToken([
            'title' => '(',
            'preg' => '(',
        ], false));

        $this->parseExpression();

        $this->actualNode->addToken($this->expectToken([
            'title' => ')',
            'preg' => ')',
        ], false));

        $this->actualNode->addToken($this->expectToken([
            'title' => 'do',
            'preg' => 'do',
        ], false));

        if ($token = $this->expectToken([
            'title' => 'Ключевое слово begin',
            'preg' => 'begin',
        ], false, false))
        {
            $node = $this->actualNode;
            $this->actualNode = new Node('Блок кода');
            $this->actualNode->addToken($token);
            $this->parseBegin();
            $this->actualNode->addToken($this->expectToken([
                'title' => ';',
                'preg' => ';',
            ], false));
            $node->addNode($this->actualNode);
            $this->actualNode = $node;
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
        $this->actualNode->addToken($this->expectToken([
            'title' => 'Оператор :=',
            'preg' => ':=',
        ], false));

        $this->parseExpression();

        $this->actualNode->addToken($this->expectToken([
            'title' => ';',
            'preg' => ';',
        ], false));
    }

    protected function parseExpression()
    {
        $node = $this->actualNode;
        $this->actualNode = new Node('Выражение');
        while ($token = ($this->expectToken(Token::$lexems[5], true, false) ?: $this->expectToken(Token::$lexems[4], true, false)))
        {
            $this->actualNode->addToken($token);
            $token = $this->expectToken(Token::$lexems[2], true, false);
            if (!$token)
            {
                $node->addNode($this->actualNode);
                $this->actualNode = $node;
                break;
            }
            else
            {
                $this->actualNode->addToken($token);
            }
        }
    }

    public function formAns()
    {
        return 'test';
    }
}
