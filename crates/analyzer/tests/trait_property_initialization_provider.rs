#![allow(clippy::expect_used, clippy::missing_panics_doc)]

use std::borrow::Cow;
use std::sync::Arc;
use std::sync::LazyLock;

use mago_allocator::LocalArena;
use mago_analyzer::Analyzer;
use mago_analyzer::analysis_result::AnalysisResult;
use mago_analyzer::plugin::PluginRegistry;
use mago_analyzer::plugin::provider::PropertyInitializationProvider;
use mago_analyzer::plugin::provider::Provider;
use mago_analyzer::plugin::provider::ProviderMeta;
use mago_analyzer::settings::Settings;
use mago_codex::metadata::class_like::ClassLikeMetadata;
use mago_codex::metadata::property::PropertyMetadata;
use mago_codex::populator::populate_codebase;
use mago_codex::scanner::scan_program;
use mago_database::file::File;
use mago_database::file::FileType;
use mago_names::resolver::NameResolver;
use mago_php_version::PHPVersion;
use mago_prelude::Prelude;
use mago_syntax::parser::parse_file;
use mago_word::WordSet;
use mago_word::word;

static PRELUDE: LazyLock<Prelude> = LazyLock::new(Prelude::build);

static META: ProviderMeta =
    ProviderMeta::new("test::controller-injects", "Controller injects", "Controllers initialize");

const SOURCE: &str = "<?php

declare(strict_types=1);

final class Service {}

abstract class Controller {}

trait InjectsService
{
    public Service $service;
}

final class HomeController extends Controller
{
    use InjectsService;
}

final class Widget
{
    use InjectsService;
}
";

struct ControllersInitialize;

impl Provider for ControllersInitialize {
    fn meta() -> &'static ProviderMeta {
        &META
    }
}

impl PropertyInitializationProvider for ControllersInitialize {
    fn is_property_initialized(&self, class_metadata: &ClassLikeMetadata, _property: &PropertyMetadata) -> bool {
        class_metadata.has_parent(word("controller"))
    }
}

#[test]
fn provider_judges_a_trait_property_by_the_class_using_the_trait() {
    let mut registry = PluginRegistry::with_library_providers();
    registry.register_property_initialization_provider(ControllersInitialize);

    let file = Arc::new(File::new(
        Cow::Borrowed(b"src/trait-property.php"),
        FileType::Host,
        None,
        Cow::Borrowed(SOURCE.as_bytes()),
    ));
    let arena = LocalArena::new();
    let program = parse_file(&arena, &file);
    assert!(!program.has_errors(), "the trait property fixture should parse");
    let resolved_names = NameResolver::new(&arena).resolve(program);
    let mut codebase = PRELUDE.metadata.clone();
    codebase.extend(scan_program(&arena, &file, program, &resolved_names, PHPVersion::PHP85));
    let mut symbol_references = PRELUDE.symbol_references.clone();
    populate_codebase(&mut codebase, &mut symbol_references, WordSet::default(), foldhash::HashSet::default());

    let mut settings = Settings::new(PHPVersion::PHP85);
    settings.find_unused_definitions = false;
    settings.check_property_initialization = true;
    settings.check_property_initialization_in_declaring_class = true;
    let analyzer = Analyzer::new(&arena, &file, &resolved_names, &codebase, &registry, settings);
    let mut result = AnalysisResult::new(symbol_references);
    analyzer.analyze(program, &mut result).expect("analysis failed");

    let uninitialized = result
        .issues
        .iter()
        .filter(|issue| issue.code.as_deref() == Some("uninitialized-property"))
        .map(|issue| issue.message.clone())
        .collect::<Vec<_>>();

    assert_eq!(uninitialized, ["Property `$service` is not initialized in the constructor of class `Widget`."]);
}
