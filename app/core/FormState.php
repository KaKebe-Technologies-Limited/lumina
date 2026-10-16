<?php
declare(strict_types=1);

namespace App\Core;

/**
 * Validation errors + old input that survive ONE redirect (flashed by App::run when a ValidationException is thrown).
 * Views use the helpers error('field'), has_error('field'), old('field', $default), errors().
 * To re-render a form on the SAME request (no redirect) call FormState::now($errors, $old).
 */
final class FormState
{
    /** @var array<string,string>|null */
    private static ?array $errors = null;
    /** @var array<string,mixed>|null */
    private static ?array $old = null;

    /** @return array<string,string> */
    public static function errors(): array
    {
        return self::$errors ??= (array) Session::getFlash('_errors', []);
    }

    /** @return array<string,mixed> */
    public static function old(): array
    {
        return self::$old ??= (array) Session::getFlash('_old', []);
    }

    /** Make errors/old input available to the next request. */
    public static function flash(array $errors, array $old): void
    {
        Session::flash('_errors', $errors);
        Session::flash('_old', $old);
    }

    /** Make errors/old input available to views rendered during THIS request. */
    public static function now(array $errors, array $old = []): void
    {
        self::$errors = $errors;
        self::$old = $old;
    }
}
