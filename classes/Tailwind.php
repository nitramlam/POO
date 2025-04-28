<?php


class Tailwind
{
    public static function includeCdn(): string
    {
        return '
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <script src="https://cdn.tailwindcss.com"></script>
        ';
    }
}