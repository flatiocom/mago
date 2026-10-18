use std::rc::Rc;
use std::sync::Arc;
use std::sync::LazyLock;

use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::array::TArray;
use mago_codex::ttype::atomic::array::keyed::TKeyedArray;
use mago_codex::ttype::atomic::array::list::TList;
use mago_codex::ttype::get_arraykey;
use mago_codex::ttype::get_mixed;
use mago_codex::ttype::get_one_int;
use mago_codex::ttype::get_string;
use mago_codex::ttype::get_truthy_string;
use mago_codex::ttype::union::TUnion;
use mago_word::Word;
use mago_word::WordMap;
use mago_word::word;

std::thread_local! {
    static SUPERGLOBALS_MAP: LazyLock<WordMap<Rc<TUnion>>> = LazyLock::new(|| {
        let mut map = WordMap::default();

        map.insert(word("$argv"), Rc::new({
            let mut type_union = TUnion::from_vec(vec![
                TAtomic::Null,
                TAtomic::Array(TArray::List(TList::new_non_empty(Arc::new(get_string())))),
            ]);

            type_union.set_ignore_nullable_issues(true);
            type_union
        }));

        map.insert(word("$argc"), Rc::new({
            let mut type_union = get_one_int();

            type_union.set_ignore_nullable_issues(true);
            type_union
        }));

        map.insert(word("$http_response_header"), Rc::new({
            let mut type_union =
                TUnion::from_atomic(TAtomic::Array(TArray::List(TList::new_non_empty(Arc::new(get_truthy_string())))));

            type_union.set_possibly_undefined(true, None);
            type_union
        }));

        let superglobal_type_union = Rc::new(TUnion::from_atomic(TAtomic::Array(TArray::Keyed(
            TKeyedArray::new_with_parameters(Arc::new(get_arraykey()), Arc::new(get_mixed())),
        ))));

        for name in ["$GLOBALS", "$_GET", "$_POST", "$_REQUEST", "$_COOKIE", "$_SERVER", "$_ENV", "$_FILES", "$_SESSION"] {
            map.insert(word(name), Rc::clone(&superglobal_type_union));
        }

        map
    });
}

pub fn get_super_globals() -> WordMap<Rc<TUnion>> {
    SUPERGLOBALS_MAP.with(|map| WordMap::clone(map))
}

pub fn get_global_variable_type(variable_name: Word) -> Option<Rc<TUnion>> {
    SUPERGLOBALS_MAP.with(|map| map.get(&variable_name).cloned())
}
