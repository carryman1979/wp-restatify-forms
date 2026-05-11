<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

final class RestatifyFormsUiTest extends TestCase {
    protected function setUp(): void {
        parent::setUp();
        $GLOBALS['rsfm_test_can_edit_posts'] = true;
    }

    public function testNormalizeTriggerForPickerConvertsFragmentToAbsoluteUrl(): void {
        $reflection = new ReflectionClass(Restatify_Forms_UI::class);
        $instance = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('normalize_trigger_for_picker');

        $normalized = $method->invoke($instance, '#restatify-form-kontaktformular');

        self::assertSame(
            'http://localhost/restatify.tech/#restatify-form-kontaktformular',
            $normalized
        );
    }

    public function testNormalizeTriggerForPickerKeepsExternalUrlsUntouched(): void {
        $reflection = new ReflectionClass(Restatify_Forms_UI::class);
        $instance = $reflection->newInstanceWithoutConstructor();
        $method = $reflection->getMethod('normalize_trigger_for_picker');

        $normalized = $method->invoke($instance, 'https://example.org/#foo');

        self::assertSame('https://example.org/#foo', $normalized);
    }

    public function testExtendWpLinkQueryAppendsFormEntryWithUrlAndPermalink(): void {
        $forms = [
            [
                'id' => 'kontaktformular',
                'title' => 'Kontaktformular',
                'trigger' => '#restatify-form-kontaktformular',
            ],
        ];

        $ui = new Restatify_Forms_UI(new Restatify_Forms_Options($forms));

        $results = $ui->extend_wp_link_query([], ['s' => 'kontakt']);

        self::assertCount(1, $results);
        self::assertSame(
            'http://localhost/restatify.tech/#restatify-form-kontaktformular',
            $results[0]['url']
        );
        self::assertSame(
            'http://localhost/restatify.tech/#restatify-form-kontaktformular',
            $results[0]['permalink']
        );
    }

    public function testExtendWpLinkQueryDoesNotAlterResultsWithoutPermission(): void {
        $GLOBALS['rsfm_test_can_edit_posts'] = false;

        $forms = [
            [
                'id' => 'kontaktformular',
                'title' => 'Kontaktformular',
                'trigger' => '#restatify-form-kontaktformular',
            ],
        ];

        $ui = new Restatify_Forms_UI(new Restatify_Forms_Options($forms));

        $seed = [
            [
                'ID' => 9,
                'title' => 'Home',
                'permalink' => 'https://localhost/restatify.tech/',
                'info' => 'Seite',
            ],
        ];

        $results = $ui->extend_wp_link_query($seed, ['s' => 'kontakt']);

        self::assertSame($seed, $results);
    }
}
