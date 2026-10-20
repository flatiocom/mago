<?php

declare(strict_types=1);

namespace AliasStub;

interface Response
{
    public function send(): void;
}

/** @mago-expect analysis:impossible-condition */
if (false) {
    /** @deprecated use Response */
    interface IResponse extends Response
    {
    }
} elseif (!interface_exists(IResponse::class)) {
    class_alias(Response::class, IResponse::class);
}

/** @mago-expect analysis:impossible-condition */
if (false) {
    /** @deprecated */
    interface IHelperOnly
    {
    }
}

final class TextResponse implements IResponse
{
    public function send(): void
    {
    }
}

function alias_stub_send(IResponse $response): Response
{
    $response->send();

    return $response;
}

/** @mago-expect analysis:deprecated-class */
final class HelperOnly implements IHelperOnly
{
}
