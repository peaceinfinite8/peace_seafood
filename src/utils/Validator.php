<?php

declare(strict_types=1);

namespace App\Utils;

/**
 * Input Validator
 */
class Validator
{
    private array $errors = [];

    public function validate(array $data, array $rules): bool
    {
        $this->errors = [];

        foreach ($rules as $field => $fieldRules) {
            $value = $data[$field] ?? null;

            foreach ($fieldRules as $rule => $param) {
                // Support shorthand: ['required', 'email'] or ['required' => true]
                if (is_int($rule)) {
                    $rule  = $param;
                    $param = true;
                }

                $this->applyRule($field, $value, $rule, $param, $data);
            }
        }

        return empty($this->errors);
    }

    private function applyRule(string $field, mixed $value, string $rule, mixed $param, array $data): void
    {
        $label = ucfirst(str_replace('_', ' ', $field));

        switch ($rule) {
            case 'required':
                if ($param && ($value === null || $value === '' || $value === [])) {
                    $this->errors[$field] = "{$label} wajib diisi";
                }
                break;

            case 'email':
                if ($value !== null && $value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
                    $this->errors[$field] = "Format email tidak valid";
                }
                break;

            case 'numeric':
                if ($value !== null && $value !== '' && !is_numeric($value)) {
                    $this->errors[$field] = "{$label} harus berupa angka";
                }
                break;

            case 'integer':
                if ($value !== null && $value !== '' && !ctype_digit((string) $value)) {
                    $this->errors[$field] = "{$label} harus berupa bilangan bulat";
                }
                break;

            case 'min':
                if ($value !== null && $value !== '' && is_numeric($value) && (float) $value < (float) $param) {
                    $this->errors[$field] = "{$label} minimal {$param}";
                }
                break;

            case 'max':
                if ($value !== null && $value !== '' && is_numeric($value) && (float) $value > (float) $param) {
                    $this->errors[$field] = "{$label} maksimal {$param}";
                }
                break;

            case 'min_length':
                if ($value !== null && strlen((string) $value) < (int) $param) {
                    $this->errors[$field] = "{$label} minimal {$param} karakter";
                }
                break;

            case 'max_length':
                if ($value !== null && strlen((string) $value) > (int) $param) {
                    $this->errors[$field] = "{$label} maksimal {$param} karakter";
                }
                break;

            case 'in':
                if ($value !== null && $value !== '' && !in_array($value, (array) $param, true)) {
                    $allowed = implode(', ', (array) $param);
                    $this->errors[$field] = "{$label} harus salah satu dari: {$allowed}";
                }
                break;

            case 'date':
                if ($value !== null && $value !== '') {
                    $d = \DateTime::createFromFormat('Y-m-d', $value);
                    if (!$d || $d->format('Y-m-d') !== $value) {
                        $this->errors[$field] = "Format tanggal {$label} tidak valid (YYYY-MM-DD)";
                    }
                }
                break;

            case 'positive':
                if ($value !== null && $value !== '' && is_numeric($value) && (float) $value <= 0) {
                    $this->errors[$field] = "{$label} harus lebih dari 0";
                }
                break;

            case 'confirmed':
                $confirmField = $field . '_confirmation';
                if ($value !== ($data[$confirmField] ?? null)) {
                    $this->errors[$field] = "{$label} tidak cocok dengan konfirmasi";
                }
                break;
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    // Backwards compatible aliases
    public function fails(): bool
    {
        return $this->hasErrors();
    }

    public function errors(): array
    {
        return $this->getErrors();
    }

    /** Static shorthand */
    public static function make(array $data, array $rules): self
    {
        $v = new self();
        $v->validate($data, $rules);
        return $v;
    }
}
