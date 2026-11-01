<?php

declare(strict_types=1);

namespace ElseifAndSwitchMergesLinkVariables;

final class Member
{
    public function delete(): void
    {
    }
}

function elseif_chain(int $kind): void
{
    if ($kind === 1) {
        $member = new Member();
        $created = true;
    } elseif ($kind === 2) {
        $member = new Member();
        $created = true;
    } else {
        $member = null;
        $created = false;
    }

    if ($created) {
        if ($member !== null) { // @mago-expect analysis:redundant-comparison
            $member->delete();
        }
    }
}

function elseif_chain_without_else(int $kind): void
{
    $member = null;
    $created = false;
    if ($kind === 1) {
        $member = new Member();
        $created = true;
    } elseif ($kind === 2) {
        $member = new Member();
        $created = true;
    }

    if ($created) {
        if ($member !== null) { // @mago-expect analysis:redundant-comparison
            $member->delete();
        }
    }
}

function elseif_branch_sets_the_guard_alone(int $kind): void
{
    if ($kind === 1) {
        $member = new Member();
        $created = true;
    } elseif ($kind === 2) {
        $member = null;
        $created = true;
    } else {
        $member = null;
        $created = false;
    }

    if ($created) {
        if ($member !== null) {
            $member->delete();
        }
    }
}

function switch_with_default(int $kind): void
{
    switch ($kind) {
        case 1:
            $member = new Member();
            $created = true;
            break;
        default:
            $member = null;
            $created = false;
    }

    if ($created) {
        if ($member !== null) { // @mago-expect analysis:redundant-comparison
            $member->delete();
        }
    }
}

function switch_without_default(int $kind): void
{
    $member = null;
    $created = false;
    switch ($kind) {
        case 1:
            $member = new Member();
            $created = true;
            break;
        case 2:
            $member = new Member();
            $created = true;
            break;
    }

    if ($created) {
        if ($member !== null) { // @mago-expect analysis:redundant-comparison
            $member->delete();
        }
    }
}

function switch_case_with_an_earlier_break(int $kind, bool $flag): void
{
    switch ($kind) {
        case 1:
            $member = new Member();
            $created = true;
            if ($flag) {
                $member = null;
                break;
            }

            break;
        default:
            $member = null;
            $created = false;
    }

    if ($created) {
        if ($member !== null) {
            $member->delete();
        }
    }
}
