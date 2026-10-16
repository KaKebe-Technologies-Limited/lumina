<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Plain-PHP templates with layouts, sections and shared data. Views live in app/views/<name>.php (no extension in the name).
 *
 *   View::render('web/about/index', ['team' => $team], 'layouts/main')   -> string
 *   View::share('meta', $meta);                                           // available in EVERY template/partial/layout
 *
 * Inside a template `$this` is the View. Data keys become local variables. Escape everything: <?= e($x) ?>.
 *   <?= partial('partials/cta-donate', ['title' => $t]) ?>      // same as $this->partial(...); returns a string, NOT echoed
 *   <?php $this->start('head'); ?> <link ...> <?php $this->end(); ?>   // define a section (layout prints <?= $this->section('head') ?>)
 *   <?php $this->layout('layouts/admin_login'); ?>              // switch layout from inside a view; $this->layout(null) = none
 * Layouts get the rendered page as $content.
 */
final class View
{
    /** @var array<string,mixed> */
    private static array $shared = [];
    /** @var array<int,self> */
    private static array $active = [];

    /** @var array<string,string> */
    private array $sections = [];
    /** @var string[] */
    private array $open = [];
    private ?string $layout = null;

    public static function share(string|array $key, mixed $value = null): void
    {
        if (is_array($key)) {
            self::$shared = array_merge(self::$shared, $key);
            return;
        }
        self::$shared[$key] = $value;
    }

    public static function shared(string $key, mixed $default = null): mixed
    {
        return self::$shared[$key] ?? $default;
    }

    public static function exists(string $name): bool
    {
        return preg_match('~^[A-Za-z0-9_\-/]+$~', $name) === 1 && !str_contains($name, '..') && is_file(APP_PATH . '/views/' . $name . '.php');
    }

    public static function render(string $view, array $data = [], ?string $layout = null): string
    {
        $v = new self();
        $v->layout = $layout;
        self::$active[] = $v;
        try {
            $content = $v->capture($view, array_merge(self::$shared, $data));
            if ($v->layout !== null) {
                return $v->capture($v->layout, array_merge(self::$shared, $data, ['content' => $content]));
            }
            return $content;
        } finally {
            array_pop(self::$active);
        }
    }

    /** Render a partial from anywhere (helper partial()). Returns the HTML string. */
    public static function partial(string $name, array $data = []): string
    {
        $v = self::$active !== [] ? end(self::$active) : new self();
        return $v->capture($name, array_merge(self::$shared, $data));
    }

    public function layout(?string $name): void
    {
        $this->layout = $name;
    }

    public function start(string $name): void
    {
        $this->open[] = $name;
        ob_start();
    }

    public function end(): void
    {
        $name = array_pop($this->open);
        $html = (string) ob_get_clean();
        if ($name !== null) {
            $this->sections[$name] = ($this->sections[$name] ?? '') . $html;
        }
    }

    public function section(string $name, string $default = ''): string
    {
        return $this->sections[$name] ?? $default;
    }

    public function hasSection(string $name): bool
    {
        return isset($this->sections[$name]);
    }

    /** @param array<string,mixed> $vars */
    private function capture(string $name, array $vars): string
    {
        if (!self::exists($name)) {
            throw new \RuntimeException("View not found: {$name}");
        }
        $__file = APP_PATH . '/views/' . $name . '.php';
        $level = ob_get_level();
        ob_start();
        try {
            (function () use ($__file, $vars): void {
                extract($vars, EXTR_SKIP);
                include $__file;
            })->call($this);
        } catch (\Throwable $e) {
            while (ob_get_level() > $level) {
                ob_end_clean();
            }
            throw $e;
        }
        return (string) ob_get_clean();
    }
}
