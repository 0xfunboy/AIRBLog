<?php
declare(strict_types=1);

namespace App\Support;

final class Validator
{
    /**
     * @param array<string, mixed> $data
     * @param array<string, array<string, mixed>> $rules
     * @return array<string, string>
     */
    public static function validate(array $data, array $rules): array
    {
        $errors = [];

        foreach ($rules as $field => $options) {
            $value = trim((string)($data[$field] ?? ''));

            if (($options['required'] ?? false) && $value === '') {
                $errors[$field] = $options['message'] ?? 'This field is required.';
                continue;
            }

            if ($value === '') {
                continue;
            }

            if (($options['email'] ?? false) && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                $errors[$field] = $options['message'] ?? 'Please provide a valid email address.';
                continue;
            }

            $max = $options['max'] ?? null;
            if ($max !== null && mb_strlen($value) > $max) {
                $errors[$field] = $options['message'] ?? sprintf('Must be less than %d characters.', $max);
            }
        }

        return $errors;
    }
}
