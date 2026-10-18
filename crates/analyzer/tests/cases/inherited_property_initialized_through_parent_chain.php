<?php

declare(strict_types=1);

abstract class Widget
{
    // @mago-expect analysis:uninitialized-property
    protected string $template;

    public function __construct(string $template)
    {
        $this->template = $template;
    }
}

abstract class FormWidget extends Widget
{
    public function __construct(string $template)
    {
        parent::__construct($template);
    }
}

final class OrderForm extends FormWidget
{
    public function __construct(string $template)
    {
        parent::__construct($template);
    }
}

abstract class DetachedWidget extends Widget
{
    public function __construct()
    {
    }
}

final class DetachedForm extends DetachedWidget
{
    public function __construct()
    {
        parent::__construct();
    }
}
