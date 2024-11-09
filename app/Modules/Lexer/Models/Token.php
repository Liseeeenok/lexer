<?php

namespace App\Modules\Lexer\Models;

class Token
{
    public static array $lexems = [
        1 => [
            'title' => 'Ключевое слово',
            'preg' => '(program|var|integer|string|begin|end|while|do|if|then|else|writeln|const)\b',
            'code' => 1,
        ],
        2 => [
            'title' => 'Оператор',
            'preg' => '(\*|\+|\-|/|:=|\<|=|\>)',
            'code' => 2,
        ],
        3 => [
            'title' => 'Разделитель',
            'preg' => '(\(|\)|,|\.|:|;)',
            'code' => 3,
        ],
        4 => [
            'title' => 'Литерал',
            'preg' => "(\d+|'[^']*')",
            'code' => 4,
        ],
        5 => [
            'title' => 'ID',
            'preg' => '([a-zA-Z_]\w*)',
            'code' => 5,
        ],
    ];

    public static array $translatePsevdocode = [
        'begin' => '',
        ':=' => '=',
        ';' => "\n",
        'while' => 'Пока',
        'do' => "\n",
        'end' => "Конец конуструкции \n",
        'if' => 'Если',
        'then' => "\n",
        'else' => "Иначе \n",
        'writeln' => 'Вывести',
        '.' => 'Конец программы',
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

    public function formAns(int $level)
    {
        $indent = str_repeat(" ", $level);
        return $indent . "|-" . "Токен: " . $this->lexeme . "\n";
    }

    public function formPsevdocode(int $level, bool $tab = false)
    {
        $text = isset(static::$translatePsevdocode[$this->lexeme]) ? static::$translatePsevdocode[$this->lexeme] : $this->lexeme;

        if ($text === '')
        {
            return '';
        }

        $indent = $tab ? str_repeat(" ", $level) : ' ';
        return $indent . $text;
    }
}
