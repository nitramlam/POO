<?php

class Tailwind
{
    // Retourne le code HTML pour intégrer Tailwind via CDN
    public static function includeCdn(): string
    {
        return '
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <script src="https://cdn.tailwindcss.com"></script>
        ';
    }
}