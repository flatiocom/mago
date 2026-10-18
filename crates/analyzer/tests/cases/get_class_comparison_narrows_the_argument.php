<?php

declare(strict_types=1);

class Token
{
}

final class RulesetToken extends Token
{
    public int $line = 0;
}

/**
 * @param list<Token> $tokens
 */
function lines(array $tokens, Token $token): int
{
    $total = 0;
    for ($i = 0, $count = count($tokens); $i < $count; $i++) {
        if (get_class($tokens[$i]) === 'RulesetToken') {
            $total += $tokens[$i]->line;
        }
    }

    if (RulesetToken::class === \get_class($token)) {
        $total += $token->line;
    }

    return $total;
}
