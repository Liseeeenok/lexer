<?php

namespace App\Livewire;

use App\Modules\Lexer\Helpers\Compiler as HelpersCompiler;
use Livewire\Component;
use App\Modules\Lexer\Helpers\HashTable;
use App\Modules\Lexer\Helpers\Lexer;
use App\Modules\Lexer\Helpers\Syntaxer;

class Compiler extends Component
{
    protected Lexer $lexer;

    protected Syntaxer $syntaxer;

    protected HelpersCompiler $compiler;

    protected HashTable $table;

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

    public function render()
    {
        return view('livewire.compiler');
    }

    public function parseLexems()
    {
        $this->lexer = new Lexer($this->text);

        $this->ans = $this->lexer->formAns();

        $this->table = $this->lexer->getTable();
    }

    public function parseSyntaxis()
    {
        $this->parseLexems();
        try
        {
            $this->syntaxer = new Syntaxer($this->table);

            $this->ans = $this->syntaxer->formAns();
        }
        catch (\Exception $e)
        {
            $this->ans = $e->getMessage();
        }
    }

    public function compile()
    {
        $this->parseSyntaxis();
        try
        {
            $this->compiler = new HelpersCompiler($this->syntaxer->getMainNode());

            $this->ans = $this->compiler->formPsevdocode();
        }
        catch (\Exception $e)
        {
            dd($e);
            $this->ans = $e->getMessage();
        }
    }
}
