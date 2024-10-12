<?php

namespace App\Modules\Lexer\Models;

class Token
{
    public static array $lexems = [
        1 => [
            'title' => 'Ключевое слово',
            'preg' => '(program|var|integer|string|begin|end|while|do|if|then|else|writeln|const)\b',
        ],
        2 => [
            'title' => 'Оператор',
            'preg' => '(\*|\+|\-|/|:=|\<|=|\>)',
        ],
        3 => [
            'title' => 'Разделитель',
            'preg' => '(\(|\)|,|\.|:|;)',
        ],
        4 => [
            'title' => 'Литерал',
            'preg' => "(\d+|'[^']*')",
        ],
        5 => [
            'title' => 'ID',
            'preg' => '([a-zA-Z_]\w*)',
        ],
    ];

    public function __construct(
        public string $lexeme,
        public int $code,
        public int $start,
    ) {
    }

    public function toTableView()
    {
        return [
            'lexeme' => $this->lexeme,
            'type' => static::$lexems[$this->code]['title'],
            'code' => $this->code,
            'start' => $this->start,
        ];
    }
}
