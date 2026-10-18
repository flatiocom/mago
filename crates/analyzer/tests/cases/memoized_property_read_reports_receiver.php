<?php

declare(strict_types=1);

final class Project
{
    public string $type = '';

    public ?string $name = null;
}

final class Row
{
    public ?Project $project = null;

    public int $id = 1;
}

final class Source
{
    public function row(): Row
    {
        return new Row();
    }
}

function takes(?string $value): void
{
    echo $value ?? '';
}

function takesInt(?int $value): void
{
    echo $value ?? 0;
}

function reports_every_read_of_a_nullable_receiver(Row $row): void
{
    takes($row->project->type); // @mago-expect analysis:possibly-null-property-access
    takes($row->project->type); // @mago-expect analysis:possibly-null-property-access
}

function reports_every_read_through_a_nullable_variable(?Row $row): void
{
    takesInt($row->id); // @mago-expect analysis:possibly-null-property-access
    takesInt($row->id); // @mago-expect analysis:possibly-null-property-access
}

function reports_every_read_through_a_method_result(Source $source): void
{
    takes($source->row()->project->type); // @mago-expect analysis:possibly-null-property-access
    takes($source->row()->project->type); // @mago-expect analysis:possibly-null-property-access
}

function reports_a_match_subject_once(Row $row): int
{
    return match ($row->project->type) { // @mago-expect analysis:possibly-null-property-access
        'a' => 1,
        'b' => 2,
        default => 3,
    };
}

function stays_silent_once_the_receiver_is_narrowed(Row $row): void
{
    takes($row->project->type); // @mago-expect analysis:possibly-null-property-access
    if ($row->project === null) {
        return;
    }

    takes($row->project->type);
}

function stays_silent_inside_isset(Row $row): bool
{
    takes($row->project->name); // @mago-expect analysis:possibly-null-property-access

    return isset($row->project->name);
}

function reports_every_read_of_a_mixed_receiver(mixed $row): void
{
    var_dump($row->id); // @mago-expect analysis:mixed-property-access
    var_dump($row->id); // @mago-expect analysis:mixed-property-access
}
