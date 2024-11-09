<?php

namespace App\Modules\Lexer\Models;

class Node
{
    protected array $lists = [];

    protected int $level = 0;

    public static $knots = [
        'Выражение' => '',
        'Блок кода' => '',
        'Функция writeln' => '',
        'Блок if' => '',
        'Цикл while' => '',
        'Присваивание переменной' => '',
        'Программа:' => 'Начало',
        'Название программы' => 'skip',
        'Объявление переменных' => 'skip',
        'Тело программы' => '',
    ];

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

    public function formPsevdocode(int $level, bool $tab = true)
    {
        if (static::$knots[$this->name] === 'skip')
        {
            return '';
        }

        $newStr = static::$knots[$this->name] === '' ? '' : "\n";

        $indent = static::$knots[$this->name] === '' ? '' : str_repeat(" ", $level);
        $ans = $indent . static::$knots[$this->name] . $newStr;

        foreach ($this->lists as $list)
        {
            $text = $list->formPsevdocode($level + 1, $tab);
            $ans .=  $text;
            $tab = str_ends_with($text, "\n") || $text === '';
        }

        return $ans;
    }
}
