use mago_codex::ttype::atomic::TAtomic;
use mago_codex::ttype::atomic::scalar::TScalar;
use mago_codex::ttype::atomic::scalar::bool::TBool;
use mago_codex::ttype::get_bool;
use mago_codex::ttype::union::TUnion;

use crate::plugin::context::InvocationInfo;
use crate::plugin::context::ProviderContext;
use crate::plugin::provider::Provider;
use crate::plugin::provider::ProviderMeta;
use crate::plugin::provider::method::MethodReturnTypeProvider;
use crate::plugin::provider::method::MethodTarget;

static META: ProviderMeta = ProviderMeta::new(
    "php::simple_xml::as_xml",
    "SimpleXMLElement::asXML",
    "Returns string|false without a filename and bool with one, like PHPStan",
);

static TARGETS: [MethodTarget; 1] = [MethodTarget::any_class(b"asXML")];

#[derive(Default)]
pub struct SimpleXmlElementAsXmlProvider;

impl Provider for SimpleXmlElementAsXmlProvider {
    fn meta() -> &'static ProviderMeta {
        &META
    }
}

impl MethodReturnTypeProvider for SimpleXmlElementAsXmlProvider {
    fn targets() -> &'static [MethodTarget] {
        &TARGETS
    }

    fn get_return_type(
        &self,
        context: &ProviderContext<'_, '_, '_>,
        class_name: &[u8],
        _method_name: &[u8],
        invocation: &InvocationInfo<'_, '_, '_>,
    ) -> Option<TUnion> {
        if !context.is_instance_of(class_name, b"SimpleXMLElement") {
            return None;
        }

        if invocation.argument_count() == 1 {
            return Some(get_bool());
        }

        Some(TUnion::from_vec(vec![
            TAtomic::Scalar(TScalar::string()),
            TAtomic::Scalar(TScalar::Bool(TBool::r#false())),
        ]))
    }
}
