#![allow(clippy::expect_used, clippy::missing_panics_doc)]

use std::borrow::Cow;
use std::num::NonZeroUsize;
use std::path::Path;
use std::path::PathBuf;
use std::sync::Arc;

use mago_allocator::LocalArena;
use mago_database::file::File;
use mago_extension::WorkerCommand;
use mago_extension::WorkerPool;
use mago_extension::WorkerPoolOptions;
use mago_linter::Linter;
use mago_linter::external::ExternalLinter;
use mago_linter::settings::Settings;
use mago_names::resolver::NameResolver;
use mago_php_version::PHPVersion;
use mago_syntax::parser::parse_file;

mod common;

const SOURCES: [&str; 2] = ["composer/src/Sdk/Syntax", "composer/src/Sdk/Internal/Syntax"];

const RULES: [&str; 2] = ["mago-sdk-test/syntax-lookups-in-file", "mago-sdk-test/syntax-lookups-in-functions"];

fn php_files(directory: &Path) -> Vec<PathBuf> {
    let mut files = Vec::new();
    for entry in std::fs::read_dir(directory).expect("the source directory should be readable") {
        let path = entry.expect("the directory entry should be readable").path();
        if path.is_dir() {
            files.extend(php_files(&path));
        } else if path.extension().is_some_and(|extension| extension == "php") {
            files.push(path);
        }
    }

    files.sort();
    files
}

#[test]
fn kind_lookups_match_a_depth_first_walk_on_real_files() -> Result<(), Box<dyn std::error::Error>> {
    let repository = Path::new(env!("CARGO_MANIFEST_DIR"));
    if !common::php_sdk_is_available(repository, "the syntax lookup SDK test") {
        return Ok(());
    }

    let command = WorkerCommand::new("php")
        .with_argument(repository.join("composer/tests/Sdk/worker.php"))
        .with_current_directory(repository);
    let pool = WorkerPool::spawn(command, NonZeroUsize::MIN, WorkerPoolOptions::default())?;
    let external = ExternalLinter::initialize([Arc::new(pool)], PHPVersion::PHP85)?;
    let settings = Settings { php_version: PHPVersion::PHP85, ..Settings::default() };

    let mut reports = [0_u64; RULES.len()];
    let files = SOURCES.iter().flat_map(|directory| php_files(&repository.join(directory)));
    for path in files {
        let name = path.strip_prefix(repository)?.to_string_lossy().into_owned();
        let file = File::ephemeral(Cow::Owned(name.clone().into_bytes()), Cow::Owned(std::fs::read(&path)?));
        let arena = LocalArena::new();
        let program = parse_file(&arena, &file);
        let resolved_names = NameResolver::new(&arena).resolve(program);
        for (rule, count) in RULES.iter().zip(&mut reports) {
            let only = vec![(*rule).to_owned()];
            let linter = Linter::new(&arena, &settings, Some(&only), false);
            for issue in linter.lint_with_external(&file, program, &resolved_names, &external)? {
                assert_eq!(issue.message, "Lookups match", "{name}");
                *count += 1;
            }
        }
    }

    assert!(reports.iter().all(|count| *count > 0), "every rule should compare lookups: {reports:?}");

    Ok(())
}
