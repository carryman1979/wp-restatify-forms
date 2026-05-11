<?php

declare(strict_types=1);

if (!defined('ABSPATH')) {
    define('ABSPATH', __DIR__ . '/../');
}

if (!function_exists('current_user_can')) {
    function current_user_can(string $capability): bool {
        if ($capability !== 'edit_posts') {
            return false;
        }

        return (bool) ($GLOBALS['rsfm_test_can_edit_posts'] ?? true);
    }
}

if (!function_exists('home_url')) {
    function home_url(string $path = ''): string {
        return 'http://localhost/restatify.tech' . $path;
    }
}

if (!function_exists('__')) {
    function __(string $text, string $domain = ''): string {
        return $text;
    }
}

if (!function_exists('absint')) {
    function absint(int|string|float $maybeint): int {
        return abs((int) $maybeint);
    }
}

if (!class_exists('Restatify_Forms_Constants')) {
    final class Restatify_Forms_Constants {
        public const TEXT_DOMAIN = 'wp-restatify-forms';
    }
}

if (!class_exists('Restatify_Forms_Options')) {
    final class Restatify_Forms_Options {
        /** @var array<int,array<string,mixed>> */
        private array $forms;

        /** @param array<int,array<string,mixed>> $forms */
        public function __construct(array $forms = []) {
            $this->forms = $forms;
        }

        /** @return array<int,array<string,mixed>> */
        public function get_all_forms(): array {
            return $this->forms;
        }

        /** @param array<string,mixed> $form */
        public function localize_form(array $form): array {
            return $form;
        }
    }
}

require_once dirname(__DIR__) . '/includes/class-restatify-forms-ui.php';
