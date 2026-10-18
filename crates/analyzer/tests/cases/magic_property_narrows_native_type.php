<?php

declare(strict_types=1);

abstract class FormControl
{
    /** @var array<callable(bool): void> */
    public array $onResult = [];

    /** @var array<callable(bool): void> */
    public $onCancel = [];
}

/**
 * @property array<callable(array{reason: string}): void> $onResult
 * @property array<callable(array{reason: string}): void> $onCancel
 */
final class FeedbackForm extends FormControl
{
}

/**
 * @property int $title
 */
final class ConflictingTag extends FormControl
{
    public string $title = '';
}

function take_int(int $_): void {}

function test_tag_narrows_native_array(FeedbackForm $form): void
{
    $form->onResult[] = function (array $_payload): void {};
    $form->onResult[] = function (bool $_result): void {}; // @mago-expect analysis:invalid-property-assignment-value
    $form->onCancel[] = function (array $_payload): void {};
}

function test_conflicting_tag_keeps_native_type(ConflictingTag $tag): void
{
    take_int($tag->title); // @mago-expect analysis:invalid-argument
}
