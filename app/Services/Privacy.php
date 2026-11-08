<?php

namespace App\Services;

class Privacy
{
    /** Short, stable code derived from a random public ID. It reveals nothing about the person. */
    public function pseudonym(string $publicId): string
    {
        return 'PT-' . strtoupper(substr(str_replace('-', '', $publicId), 0, 8));
    }

    /** Generalize an exact age into a 10-year band; ages 90 and over are grouped together. */
    public function ageBand(int $age): string
    {
        if ($age >= 90) {
            return '90+';
        }

        $low = intdiv($age, 10) * 10;

        return $low . '-' . ($low + 9);
    }
}