#![allow(clippy::expect_used, clippy::missing_panics_doc)]

use std::borrow::Cow;
use std::sync::Arc;
use std::sync::LazyLock;

use mago_allocator::LocalArena;
use mago_analyzer::Analyzer;
use mago_analyzer::analysis_result::AnalysisResult;
use mago_analyzer::plugin::PluginRegistry;
use mago_analyzer::settings::Settings;
use mago_codex::populator::populate_codebase;
use mago_codex::scanner::scan_program;
use mago_database::file::File;
use mago_database::file::FileType;
use mago_names::resolver::NameResolver;
use mago_php_version::PHPVersion;
use mago_prelude::Prelude;
use mago_reporting::Issue;
use mago_syntax::parser::parse_file;
use mago_word::WordSet;

static PRELUDE: LazyLock<Prelude> = LazyLock::new(Prelude::build);

const SOURCE: &str = "<?php

declare(strict_types=1);

abstract class UserParent
{
    abstract public function items(): int;
}

final class UserChild extends UserParent
{
    public function items(): string
    {
        return '';
    }
}

final class TentativeChild implements IteratorAggregate
{
    public function getIterator(): array
    {
        return [];
    }
}

final class WillChangeChild implements IteratorAggregate
{
    #[\\ReturnTypeWillChange]
    public function getIterator(): array
    {
        return [];
    }
}
";

const BUILT_IN_SOURCE: &str = "<?php

declare(strict_types=1);

final class RealWillChangeChild implements Random\\Engine
{
    #[\\ReturnTypeWillChange]
    public function generate(): int
    {
        return 1;
    }
}

final class RealMissingChild implements Random\\Engine
{
    public function generate()
    {
        return '';
    }
}

final class TentativeMissingChild implements Countable
{
    public function count()
    {
        return 1;
    }
}
";

const ATTRIBUTE_LINE_SOURCE: &str = "<?php

declare(strict_types=1);

class UserParent
{
    public function value(): int { return 1; }

    public function take(int $a): void {}

    public function open(): void {}
}

final class UserChild extends UserParent
{
    #[\\Override]
    #[\\Deprecated]
    public function value(): string { return ''; }

    /** @param string $a */
    #[\\Override]
    public function take(string $a): void {}

    #[\\Override] public function open(): int { return 1; }
}

interface Shape
{
    public function area(): int;
}

final class Square implements Shape
{
    #[\\Override]
    public function area(): string { return ''; }
}
";

const IMPLICIT_RETURN_SOURCE: &str = "<?php

declare(strict_types=1);

final class Failure extends Exception
{
    public function __toString()
    {
        return 'failure';
    }
}

class Label
{
    public function __toString(): string
    {
        return 'label';
    }

    public function __invoke(): int
    {
        return 1;
    }
}

final class Caption extends Label
{
    public function __toString()
    {
        return 'caption';
    }

    public function __invoke()
    {
        return 2;
    }
}
";

fn analyze(source: &'static str) -> (Arc<File>, Vec<Issue>) {
    let file = Arc::new(File::new(
        Cow::Borrowed(b"src/return-notes.php"),
        FileType::Host,
        None,
        Cow::Borrowed(source.as_bytes()),
    ));
    let arena = LocalArena::new();
    let program = parse_file(&arena, &file);
    assert!(!program.has_errors(), "the return note fixture should parse");
    let resolved_names = NameResolver::new(&arena).resolve(program);
    let mut codebase = PRELUDE.metadata.clone();
    codebase.extend(scan_program(&arena, &file, program, &resolved_names, PHPVersion::PHP85));
    let mut symbol_references = PRELUDE.symbol_references.clone();
    populate_codebase(&mut codebase, &mut symbol_references, WordSet::default(), foldhash::HashSet::default());

    let registry = PluginRegistry::with_library_providers();
    let mut settings = Settings::new(PHPVersion::PHP85);
    settings.find_unused_definitions = false;
    let analyzer = Analyzer::new(&arena, &file, &resolved_names, &codebase, &registry, settings);
    let mut result = AnalysisResult::new(symbol_references);
    analyzer.analyze(program, &mut result).expect("analysis failed");

    (file, result.issues.into_iter().collect())
}

fn return_type_notes() -> Vec<(String, String)> {
    analyze(SOURCE)
        .1
        .iter()
        .filter(|issue| issue.code.as_deref() == Some("incompatible-return-type"))
        .map(|issue| {
            let child = issue.message.split('`').nth(3).expect("the message names the child method");

            (child.to_string(), issue.notes.join(" "))
        })
        .collect()
}

fn signature_finding_lines(source: &'static str) -> Vec<(String, u32)> {
    let (file, issues) = analyze(source);

    issues
        .iter()
        .filter(|issue| issue.code.as_deref().is_some_and(|code| code.starts_with("incompatible-")))
        .map(|issue| {
            let primary = issue.annotations.iter().find(|annotation| annotation.is_primary()).expect("a primary span");

            (issue.code.clone().expect("a code"), file.line_number(primary.span.start.offset) + 1)
        })
        .collect()
}

fn return_type_messages(source: &'static str) -> Vec<(String, String)> {
    analyze(source)
        .1
        .iter()
        .filter(|issue| issue.code.as_deref() == Some("incompatible-return-type"))
        .map(|issue| (issue.message.to_string(), issue.notes.join(" ")))
        .collect()
}

#[test]
fn return_type_note_tells_a_load_failure_from_a_tentative_type() {
    assert_eq!(
        return_type_notes(),
        [
            (
                "UserChild::items()".to_string(),
                "PHP refuses to load a class whose native types do not match the parent method's.".to_string()
            ),
            (
                "TentativeChild::getiterator()".to_string(),
                "PHP deprecates a return type that does not match the built-in parent method's, unless the method has the ReturnTypeWillChange attribute.".to_string()
            ),
            (
                "WillChangeChild::getiterator()".to_string(),
                "Return types must be covariant: child must return equal or narrower types than parent.".to_string()
            ),
        ]
    );
}

#[test]
fn return_type_will_change_spares_only_a_tentative_built_in_return() {
    assert_eq!(
        return_type_messages(BUILT_IN_SOURCE),
        [
            (
                "Return type `int` of `RealWillChangeChild::generate()` is incompatible with parent return type `string` of `Random\\Engine::generate()`".to_string(),
                "PHP refuses to load a class whose native types do not match the parent method's.".to_string()
            ),
            (
                "`RealMissingChild::generate()` must declare a return type compatible with `string` declared by `Random\\Engine::generate()`".to_string(),
                "PHP requires an overriding method to declare a return type when the overridden method declares one.".to_string()
            ),
        ]
    );
}

#[test]
fn signature_findings_start_at_the_first_attribute_line() {
    assert_eq!(
        signature_finding_lines(ATTRIBUTE_LINE_SOURCE),
        [
            ("incompatible-return-type".to_string(), 16),
            ("incompatible-parameter-type".to_string(), 21),
            ("incompatible-return-type".to_string(), 24),
            ("incompatible-return-type".to_string(), 34),
        ]
    );
}

#[test]
fn only_to_string_has_an_implicit_return_type() {
    assert_eq!(
        return_type_messages(IMPLICIT_RETURN_SOURCE),
        [(
            "`Caption::__invoke()` must declare a return type compatible with `int` declared by `Label::__invoke()`"
                .to_string(),
            "PHP requires an overriding method to declare a return type when the overridden method declares one."
                .to_string()
        )]
    );
}
