<?php

declare(strict_types=1);

interface Template
{
}

final class DefaultTemplate implements Template
{
}

class TemplateFactory
{
    /**
     * @template T of Template
     * @param class-string<T>|null $class
     * @return ($class is null ? Template : T)
     */
    public function createTemplate(?string $class = null): Template
    {
        return new DefaultTemplate();
    }

    /**
     * @return Template
     */
    public function plainTemplate(): Template
    {
        return new DefaultTemplate();
    }
}

final class AppTemplateFactory extends TemplateFactory
{
    public function createTemplate(?string $class = null): DefaultTemplate // @mago-expect analysis:incompatible-return-type
    {
        return new DefaultTemplate();
    }

    public function plainTemplate(): DefaultTemplate
    {
        return new DefaultTemplate();
    }
}
