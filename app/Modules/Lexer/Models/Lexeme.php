<?php

namespace App\Modules\Lexer\Models;

class Lexeme
{
    public static array $OPERATORS = ['*', '+', '-', '/', ':=', '<', '=', '>'];

    public static array $SEPARATOR = ['(', ')', ',', '.', ':', ';'];

    public static array $KEYWORDS = ['program', 'var', 'integer', 'string', 'begin', 'end', 'while', 'do', 'if', 'then', 'else'];

    public array $types = [
        1 => 'Ключевое слово',
        2 => 'Название программы',
        3 => 'Оператор',
        4 => 'Переменная',
        5 => 'Тип переменной',
        6 => 'Разделитель',
    ];

    public function __construct(
        public string $lexeme,
        public int $code,
    ) {
    }

    public function toTableView()
    {
        return [
            'lexeme' => $this->lexeme,
            'type' => $this->types[$this->code],
            'code' => $this->code,
        ];
    }
}
