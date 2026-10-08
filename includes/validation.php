<?php

function validate_required($value, $label)
{
    if (trim($value) === '') {
        return 'กรุณากรอก' . $label;
    }

    return '';
}

function validate_max_length($value, $label, $maxLength)
{
    if (mb_strlen(trim($value)) > $maxLength) {
        return $label . 'ต้องไม่เกิน ' . $maxLength . ' ตัวอักษร';
    }

    return '';
}

function validate_min_length($value, $label, $minLength)
{
    if (strlen($value) < $minLength) {
        return $label . 'ต้องมีอย่างน้อย ' . $minLength . ' ตัวอักษร';
    }

    return '';
}

function validate_email_format($value)
{
    if (filter_var($value, FILTER_VALIDATE_EMAIL) === false) {
        return 'รูปแบบอีเมลไม่ถูกต้อง';
    }

    return '';
}
