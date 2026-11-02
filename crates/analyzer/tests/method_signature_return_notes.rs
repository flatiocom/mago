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

fn return_type_notes() -> Vec<(String, String)> {
    let file = Arc::new(File::new(
        Cow::Borrowed(b"src/return-notes.php"),
        FileType::Host,
        None,
        Cow::Borrowed(SOURCE.as_bytes()),
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

    result
        .issues
        .iter()
        .filter(|issue| issue.code.as_deref() == Some("incompatible-return-type"))
        .map(|issue| {
            let child = issue.message.split('`').nth(3).expect("the message names the child method");

            (child.to_string(), issue.notes.join(" "))
        })
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
