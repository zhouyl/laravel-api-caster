<?php

$rules = [
    'align_multiline_comment' => true,
    'array_indentation'       => true,
    'array_syntax'            => ['syntax' => 'short'],
    'binary_operator_spaces'  => [
        'operators' => [
            '='  => 'align_single_space_minimal',
            '=>' => 'align_single_space_minimal',
            '|'  => 'no_space',
        ],
    ],
    'blank_line_after_namespace'  => true,
    'blank_line_before_statement' => [
        'statements' => [
            'break',
            'continue',
            'declare',
            'default',
            'exit',
            'do',
            'exit',
            'for',
            'foreach',
            'goto',
            'if',
            'include',
            'include_once',
            'require',
            'require_once',
            'return',
            'switch',
            'throw',
            'try',
            'while',
            'yield',
        ],
    ],
    'single_space_around_construct'           => true,
    'control_structure_braces'                => true,
    'control_structure_continuation_position' => true,
    'declare_parentheses'                     => true,
    'no_multiple_statements_per_line'         => true,
    'braces_position'                         => true,
    'statement_indentation'                   => true,
    'no_extra_blank_lines'                    => true,
    'cast_spaces'                             => true,
    'class_attributes_separation'             => [
        'elements' => [
            'const'    => 'one',
            'method'   => 'one',
            'property' => 'one',
        ],
    ],
    'combine_consecutive_issets'        => true,
    'combine_consecutive_unsets'        => true,
    'compact_nullable_type_declaration' => true,
    'concat_space'                      => ['spacing' => 'none'],
    'declare_equal_normalize'           => ['space' => 'none'],
    'dir_constant'                      => true,
    'elseif'                            => true,
    'encoding'                          => true,
    'explicit_indirect_variable'        => true,
    // 'explicit_string_variable'   => true,
    'full_opening_tag'     => true,
    'function_declaration' => true,
    'increment_style'      => [
        'style' => PhpCsFixer\Fixer\Operator\IncrementStyleFixer::STYLE_POST,
    ],
    'indentation_type'           => true,
    'is_null'                    => true,
    'line_ending'                => true,
    'list_syntax'                => ['syntax' => 'short'],
    'logical_operators'          => true,
    'constant_case'              => ['case' => 'lower'],
    'lowercase_keywords'         => true,
    'lowercase_static_reference' => true,
    'magic_constant_casing'      => true,
    'method_argument_space'      => [
        'keep_multiple_spaces_after_comma' => true,
        'after_heredoc'                    => true,
        'on_multiline'                     => 'ensure_fully_multiline',
    ],
    'modernize_types_casting'                     => true,
    'multiline_comment_opening_closing'           => true,
    'multiline_whitespace_before_semicolons'      => true,
    'native_constant_invocation'                  => false,
    'native_function_casing'                      => false,
    'native_function_invocation'                  => false,
    'new_with_parentheses'                        => false,
    'no_alias_functions'                          => true,
    'no_alternative_syntax'                       => true,
    'no_blank_lines_after_class_opening'          => true,
    'no_blank_lines_after_phpdoc'                 => true,
    'no_closing_tag'                              => true,
    'no_empty_phpdoc'                             => true,
    'no_empty_statement'                          => true,
    'no_homoglyph_names'                          => true,
    'no_leading_import_slash'                     => true,
    'no_leading_namespace_whitespace'             => true,
    'no_mixed_echo_print'                         => ['use' => 'echo'],
    'no_multiline_whitespace_around_double_arrow' => true,
    'no_null_property_initialization'             => true,
    'no_php4_constructor'                         => true,
    'no_short_bool_cast'                          => true,
    'echo_tag_syntax'                             => ['format' => 'long'],
    'no_singleline_whitespace_before_semicolons'  => true,
    'no_spaces_after_function_name'               => true,
    'spaces_inside_parentheses'                   => true,
    'no_superfluous_elseif'                       => true,
    'no_trailing_comma_in_singleline'             => true,
    'no_trailing_whitespace'                      => true,
    'no_trailing_whitespace_in_comment'           => true,
    'no_unneeded_control_parentheses'             => true,
    'no_unneeded_braces'                          => true,
    'no_unneeded_final_method'                    => true,
    'no_unreachable_default_argument_value'       => true,
    'no_unset_on_property'                        => false,
    'no_unused_imports'                           => true,
    'no_useless_else'                             => true,
    'no_useless_return'                           => true,
    'no_whitespace_before_comma_in_array'         => true,
    'no_whitespace_in_blank_line'                 => true,
    'non_printable_character'                     => true,
    'normalize_index_brace'                       => true,
    'object_operator_without_whitespace'          => true,
    'ordered_class_elements'                      => [
        'order' => [
            'use_trait',
            'constant_public',
            'constant_protected',
            'constant_private',
            'property_public_static',
            'property_protected_static',
            'property_private_static',
            'property_public',
            'property_protected',
            'property_private',
            'method_public_static',
            'construct',
            'destruct',
            'magic',
            'phpunit',
            'method_public',
            'method_protected',
            'method_private',
            'method_protected_static',
            'method_private_static',
        ],
    ],
    'ordered_imports' => [
        'imports_order' => ['const', 'class', 'function'],
    ],
    'ordered_interfaces' => [
        'direction' => 'ascend',
        'order'     => 'alpha',
    ],
    'phpdoc_add_missing_param_annotation'           => false,
    'phpdoc_align'                                  => true,
    'phpdoc_annotation_without_dot'                 => true,
    'phpdoc_indent'                                 => true,
    'phpdoc_no_access'                              => true,
    'phpdoc_no_package'                             => true,
    'phpdoc_order'                                  => true,
    'phpdoc_return_self_reference'                  => true,
    'phpdoc_scalar'                                 => true,
    'phpdoc_separation'                             => true,
    'phpdoc_single_line_var_spacing'                => true,
    'phpdoc_summary'                                => false,
    'phpdoc_to_comment'                             => true,
    'phpdoc_trim'                                   => true,
    'phpdoc_trim_consecutive_blank_line_separation' => true,
    'phpdoc_types'                                  => ['groups' => ['simple', 'meta']],
    'phpdoc_types_order'                            => true,
    'phpdoc_var_without_name'                       => true,
    'pow_to_exponentiation'                         => true,
    'protected_to_private'                          => true,
    'return_assignment'                             => true,
    'return_type_declaration'                       => ['space_before' => 'none'],
    'self_accessor'                                 => true,
    'self_static_accessor'                          => true,
    'semicolon_after_instruction'                   => true,
    'set_type_to_cast'                              => true,
    'short_scalar_cast'                             => true,
    'simplified_null_return'                        => false,
    'single_blank_line_at_eof'                      => true,
    'single_import_per_statement'                   => true,
    'single_line_after_imports'                     => true,
    'single_quote'                                  => true,
    'standardize_not_equals'                        => true,
    'ternary_to_null_coalescing'                    => true,
    'trailing_comma_in_multiline'                   => ['elements' => ['arrays']],
    'trim_array_spaces'                             => true,
    'unary_operator_spaces'                         => true,
    'visibility_required'                           => [
        'elements' => [
            'method',
            'property',
        ],
    ],
    'whitespace_after_comma_in_array' => true,
];

$finder = PhpCsFixer\Finder::create()
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true)
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests');

$config = new PhpCsFixer\Config();

return $config->setRules($rules)
    ->setRiskyAllowed(true) // The --allow-risky option (pass yes or no) allows you to set whether risky rules may run. Default value is taken from config file. Risky rule is a rule, which could change code behaviour. By default no risky rules are run.
    ->setUsingCache(false)
    ->setFinder($finder)
    ->setLineEnding("\n");
