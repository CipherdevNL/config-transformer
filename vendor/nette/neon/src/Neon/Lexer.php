<?php

/**
 * This file is part of the Nette Framework (https://nette.org)
 * Copyright (c) 2004 David Grudl (https://davidgrudl.com)
 */
declare (strict_types=1);
namespace ConfigTransformer202204164\Nette\Neon;

/** @internal */
final class Lexer
{
    public const Patterns = [
        // strings
        \ConfigTransformer202204164\Nette\Neon\Token::String => '
			\'\'\'\\n (?:(?: [^\\n] | \\n(?![\\t\\ ]*+\'\'\') )*+ \\n)?[\\t\\ ]*+\'\'\' |
			"""\\n (?:(?: [^\\n] | \\n(?![\\t\\ ]*+""") )*+ \\n)?[\\t\\ ]*+""" |
			\' (?: \'\' | [^\'\\n] )*+ \' |
			" (?: \\\\. | [^"\\\\\\n] )*+ "
		',
        // literal / boolean / integer / float
        \ConfigTransformer202204164\Nette\Neon\Token::Literal => '
			(?: [^#"\',:=[\\]{}()\\n\\t\\ `-] | (?<!["\']) [:-] [^"\',=[\\]{}()\\n\\t\\ ] )
			(?:
				[^,:=\\]})(\\n\\t\\ ]++ |
				:(?! [\\n\\t\\ ,\\]})] | $ ) |
				[\\ \\t]++ [^#,:=\\]})(\\n\\t\\ ]
			)*+
		',
        // punctuation
        \ConfigTransformer202204164\Nette\Neon\Token::Char => '[,:=[\\]{}()-]',
        // comment
        \ConfigTransformer202204164\Nette\Neon\Token::Comment => '\\#.*+',
        // new line
        \ConfigTransformer202204164\Nette\Neon\Token::Newline => '\\n++',
        // whitespace
        \ConfigTransformer202204164\Nette\Neon\Token::Whitespace => '[\\t\\ ]++',
    ];
    public function tokenize(string $input) : \ConfigTransformer202204164\Nette\Neon\TokenStream
    {
        $input = \str_replace("\r", '', $input);
        $pattern = '~(' . \implode(')|(', self::Patterns) . ')~Amixu';
        $res = \preg_match_all($pattern, $input, $tokens, \PREG_SET_ORDER);
        if ($res === \false) {
            throw new \ConfigTransformer202204164\Nette\Neon\Exception('Invalid UTF-8 sequence.');
        }
        $types = \array_keys(self::Patterns);
        $offset = 0;
        foreach ($tokens as &$token) {
            $type = null;
            for ($i = 1; $i <= \count($types); $i++) {
                if (!isset($token[$i])) {
                    break;
                } elseif ($token[$i] !== '') {
                    $type = $types[$i - 1];
                    if ($type === \ConfigTransformer202204164\Nette\Neon\Token::Char) {
                        $type = $token[0];
                    }
                    break;
                }
            }
            $token = new \ConfigTransformer202204164\Nette\Neon\Token($token[0], $type);
            $offset += \strlen($token->value);
        }
        $stream = new \ConfigTransformer202204164\Nette\Neon\TokenStream($tokens);
        if ($offset !== \strlen($input)) {
            $s = \str_replace("\n", '\\n', \substr($input, $offset, 40));
            $stream->error("Unexpected '{$s}'", \count($tokens));
        }
        return $stream;
    }
    public static function requiresDelimiters(string $s) : bool
    {
        return \preg_match('~[\\x00-\\x1F]|^[+-.]?\\d|^(true|false|yes|no|on|off|null)$~Di', $s) || !\preg_match('~^' . self::Patterns[\ConfigTransformer202204164\Nette\Neon\Token::Literal] . '$~Dx', $s);
    }
}
