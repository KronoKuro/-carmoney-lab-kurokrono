<?php

declare(strict_types=1);

namespace CarMoneyLab\Domain;

use RuntimeException;

final class ValidationException extends RuntimeException
{
    /** @param array<string,string> $errors поле => сообщение */
    public function __construct(private readonly array $errors)
    {
        parent::__construct('Заявка не прошла валидацию');
    }

    /** @return array<string,string> */
    public function errors(): array
    {
        return $this->errors;
    }
}
