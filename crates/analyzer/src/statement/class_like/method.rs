use mago_allocator::Arena;
use mago_codex::context::ScopeContext;
use mago_word::ascii_lowercase_word;
use mago_word::concat_word;

use mago_codex::identifier::method::MethodIdentifier;
use mago_codex::metadata::class_like::ClassLikeMetadata;
use mago_codex::metadata::function_like::FunctionLikeMetadata;
use mago_codex::reference::ReferenceOrigin;

use mago_span::HasSpan;
use mago_span::Span;
use mago_syntax::cst::Method;
use mago_syntax::cst::MethodBody;

use crate::analyzable::Analyzable;
use crate::artifacts::AnalysisArtifacts;
use crate::context::Context;
use crate::context::block::BlockContext;
use crate::error::AnalysisError;
use crate::statement::attributes::AttributeTarget;
use crate::statement::attributes::analyze_attributes;
use crate::statement::function_like::FunctionLikeBody;
use crate::statement::function_like::analyze_function_like;
use crate::statement::function_like::check_unused_function_template_parameters;
use crate::statement::function_like::report_parameter_template_bound_violations;
use crate::statement::function_like::unused_parameter;
use crate::statement::function_like::unused_parameter::UnusedParameterScope;
use crate::utils::missing_type_hints;

impl<'ast, 'arena> Analyzable<'ast, 'arena> for Method<'arena> {
    fn analyze<'ctx, A>(
        &'ast self,
        context: &mut Context<'ctx, 'arena, A>,
        block_context: &mut BlockContext<'ctx>,
        artifacts: &mut AnalysisArtifacts,
    ) -> Result<(), AnalysisError>
    where
        A: Arena,
    {
        analyze_attributes(
            context,
            block_context,
            artifacts,
            self.attribute_lists.as_slice(),
            AttributeTarget::Method,
        )?;

        let Some(class_like_metadata) = block_context.scope.get_class_like() else {
            tracing::error!(
                "Attempted to analyze method `{}` without class-like context.",
                mago_bytes::BytesDisplay(self.name.value)
            );

            return Ok(());
        };

        let lowercase_method_name = ascii_lowercase_word(self.name.value);
        if context.settings.diff
            && context.codebase.safe_symbol_members.contains(&(class_like_metadata.name, lowercase_method_name))
        {
            return Ok(());
        }

        let Some(method_metadata) =
            context.codebase.get_method_by_id(&MethodIdentifier::new(class_like_metadata.name, lowercase_method_name))
        else {
            tracing::error!(
                "Failed to find method metadata for `{}` in class `{}`.",
                mago_bytes::BytesDisplay(self.name.value),
                class_like_metadata.original_name
            );

            return Ok(());
        };

        // Skip duplicate methods; semantics reports the error
        if method_metadata.span != self.span() {
            return Ok(());
        }

        if let MethodBody::Concrete(concrete_body) = &self.body {
            let mut scope =
                ScopeContext::new(ReferenceOrigin::Symbol((class_like_metadata.name, method_metadata.name)));
            scope.set_class_like(Some(class_like_metadata));
            scope.set_function_like(Some(method_metadata));
            scope.set_static(self.is_static());

            let mut method_block_context = BlockContext::new(scope, context.settings.register_super_globals);

            method_block_context.flags.set_collect_initializations(true);

            analyze_function_like(
                context,
                artifacts,
                &mut method_block_context,
                method_metadata,
                &self.parameter_list,
                FunctionLikeBody::Statements(concrete_body.statements.as_slice(), concrete_body.span()),
                None,
            )?;

            let method_key = (class_like_metadata.name, lowercase_method_name);

            if lowercase_method_name.as_bytes() == b"__construct" {
                context
                    .constructor_initialized_properties
                    .insert(class_like_metadata.name, method_block_context.definitely_initialized_properties.clone());
            }

            artifacts
                .method_initialized_properties
                .insert(method_key, std::mem::take(&mut method_block_context.definitely_initialized_properties));

            artifacts
                .method_calls_this_methods
                .insert(method_key, std::mem::take(&mut method_block_context.definitely_called_methods));

            if method_block_context.flags.calls_parent_constructor() {
                artifacts.method_calls_parent_constructor.insert(method_key, true);
            }

            if let Some(parent_initializer_name) = method_block_context.calls_parent_initializer {
                artifacts.method_calls_parent_initializer.insert(method_key, parent_initializer_name);
            }

            if context.settings.find_unused_parameters
                && is_constructor_checked_for_unused_parameters(context, class_like_metadata, method_metadata)
            {
                unused_parameter::check_unused_params(
                    method_metadata,
                    self.parameter_list.parameters.as_slice(),
                    FunctionLikeBody::Statements(concrete_body.statements.as_slice(), concrete_body.span()),
                    UnusedParameterScope::Every,
                    context,
                );
            }
        }

        check_unused_function_template_parameters(context, method_metadata, self.name.span(), "method", || {
            concat_word!(&class_like_metadata.original_name, "::", self.name.value)
        });

        let declaration = Some(Span::between(self.span(), self.name.span()));
        // Check for missing type hints
        for (i, parameter) in self.parameter_list.parameters.iter().enumerate() {
            missing_type_hints::check_parameter_type_hint(
                context,
                Some(class_like_metadata),
                method_metadata,
                parameter,
            );

            missing_type_hints::check_imprecise_parameter_type_hint(
                context,
                method_metadata,
                parameter,
                i,
                declaration,
            );
        }

        missing_type_hints::check_return_type_hint(
            context,
            Some(class_like_metadata),
            method_metadata,
            self.name.value,
            self.return_type_hint.as_ref(),
            self.span(),
        );

        missing_type_hints::check_imprecise_return_type_hint(
            context,
            method_metadata,
            self.name.value,
            self.return_type_hint.as_ref(),
            declaration,
        );

        missing_type_hints::check_function_like_callable_signatures(
            context,
            method_metadata,
            self.name.span(),
            &format!("method `{}::{}`", class_like_metadata.original_name, method_metadata.original_name),
        );

        missing_type_hints::check_function_like_template_arguments(
            context,
            method_metadata,
            self.name.span(),
            &format!("method `{}::{}`", class_like_metadata.original_name, method_metadata.original_name),
        );

        report_parameter_template_bound_violations(context, method_metadata, Some(class_like_metadata.name));

        Ok(())
    }
}

fn is_constructor_checked_for_unused_parameters<A>(
    context: &Context<'_, '_, A>,
    class_like_metadata: &ClassLikeMetadata,
    method_metadata: &FunctionLikeMetadata,
) -> bool
where
    A: Arena,
{
    method_metadata.method_metadata.as_ref().is_some_and(|method| method.is_constructor)
        && class_like_metadata.attribute_flags.is_none()
        && !class_like_metadata
            .all_parent_interfaces
            .iter()
            .any(|interface| context.codebase.method_exists(interface.as_bytes(), b"__construct"))
}
