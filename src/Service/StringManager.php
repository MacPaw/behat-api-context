<?php

declare(strict_types=1);

namespace BehatApiContext\Service;

use RuntimeException;

class StringManager
{
    private const START_SEPARATOR = "{{";
    private const END_SEPARATOR = "}}";

    public function substituteValues(array $substitutionArray, string $string): string
    {
        $start = strpos($string, self::START_SEPARATOR);

        if ($start === false) {
            return $string;
        }

        $end = strpos($string, self::END_SEPARATOR, $start);

        if ($end === false) {
            throw new RuntimeException("Invalid syntax");
        }

        $key = trim(substr(
            $string,
            $start + strlen(self::START_SEPARATOR),
            $end - $start - strlen(self::START_SEPARATOR)
        ));

        if (!isset($substitutionArray[$key])) {
            throw new RuntimeException("Key not found");
        }

        $newString = substr_replace(
            $string,
            $substitutionArray[$key],
            $start,
            $end - $start + strlen(self::END_SEPARATOR)
        );

        return $this->substituteValues($substitutionArray, $newString);
    }
}
