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
use mago_codex::ttype::TType;
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

final class Total
{
    public int|string $amount = 0;
}

/** @param array{total: int|string} $row */
function element(array $row): void
{
    $row['total'] .= 'x';
}

function property(Total $total): void
{
    $total->amount .= 'x';
}

function variable(int|string $sum): void
{
    $sum .= 'x';
}
";

fn target_types() -> Vec<(&'static str, String)> {
    let file = Arc::new(File::new(
        Cow::Borrowed(b"src/compound-target.php"),
        FileType::Host,
        None,
        Cow::Borrowed(SOURCE.as_bytes()),
    ));
    let arena = LocalArena::new();
    let program = parse_file(&arena, &file);
    assert!(!program.has_errors(), "the compound target fixture should parse");
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
    let artifacts = analyzer.analyze_with_artifacts(program, &mut result).expect("analysis failed");

    ["$row['total']", "$total->amount", "$sum"]
        .into_iter()
        .map(|target| {
            let start = SOURCE.find(&format!("{target} .=")).expect("every target is in the fixture");
            let range = (u32::try_from(start).expect("offset"), u32::try_from(start + target.len()).expect("offset"));
            let target_type = artifacts.expression_types.get(&range).expect("the target has a type");

            (target, target_type.get_id().to_string())
        })
        .collect()
}

#[test]
fn compound_assignment_target_keeps_the_type_it_reads() {
    assert_eq!(
        target_types(),
        [
            ("$row['total']", "int|string".to_string()),
            ("$total->amount", "int|string".to_string()),
            ("$sum", "int|string".to_string())
        ]
    );
}
