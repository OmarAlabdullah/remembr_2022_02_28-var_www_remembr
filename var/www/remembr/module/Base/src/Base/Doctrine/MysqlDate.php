<?php

namespace Base\Doctrine;

/**
 * MysqlDate ::= "DATE" "(" ArithmeticPrimary ")"
 */
class MysqlDate extends \Doctrine\ORM\Query\AST\Functions\FunctionNode
{
    // (1)
    public $dateExpression = null;

    public function parse(\Doctrine\ORM\Query\Parser $parser)
    {
        $parser->match(\Doctrine\ORM\Query\Lexer::T_IDENTIFIER); // (2)
        $parser->match(\Doctrine\ORM\Query\Lexer::T_OPEN_PARENTHESIS); // (3)
        $this->dateExpression = $parser->ArithmeticPrimary(); // (6)
        $parser->match(\Doctrine\ORM\Query\Lexer::T_CLOSE_PARENTHESIS); // (3)
    }

    public function getSql(\Doctrine\ORM\Query\SqlWalker $sqlWalker)
    {
        return 'DATE(' .
            $this->dateExpression->dispatch($sqlWalker) .
        ')'; // (7)
    }
}
?>
