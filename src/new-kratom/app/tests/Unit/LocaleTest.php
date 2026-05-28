<?php

namespace Tests\Unit;

use App\Support\Locale;
use Tests\TestCase;

class LocaleTest extends TestCase
{
    public function test_translate_uses_requested_locale_and_falls_back_to_default(): void
    {
        $this->assertSame('Русский текст', Locale::translate([
            'cs' => 'Cesky text',
            'ru' => 'Русский текст',
        ], 'ru'));

        $this->assertSame('Cesky text', Locale::translate([
            'cs' => 'Cesky text',
            'ru' => '',
        ], 'uk'));
    }
}
