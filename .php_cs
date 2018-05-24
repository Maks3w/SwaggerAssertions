<?php

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules(
        [
            '@PHP71Migration' => true,
            '@PSR2' => true,
            'array_syntax' => [
                'syntax' => 'short'
            ],
            'binary_operator_spaces' => true,
            'blank_line_after_opening_tag' => true,
            'concat_space' => [
                'spacing' => 'one',
            ],
            'declare_strict_types' => true,
            'dir_constant' => true,
            'ereg_to_preg' => true,
            'general_phpdoc_annotation_remove' => [
                'expectedException',
                'expectedExceptionMessage',
                'expectedExceptionMessageRegExp',
            ],
            'hash_to_slash_comment' => true,
            'heredoc_to_nowdoc' => true,
            'linebreak_after_opening_tag' => true,
            'lowercase_cast' => true,
            'method_separation' => true,
            'modernize_types_casting' => true,
            'new_with_braces' => true,
            'no_alias_functions' => true,
            'no_blank_lines_after_class_opening' => true,
            'no_blank_lines_after_phpdoc' => true,
            'no_empty_comment' => true,
            'no_empty_phpdoc' => true,
            'no_empty_statement' => true,
            'no_extra_consecutive_blank_lines' => true,
            'no_leading_import_slash' => true,
            'no_leading_namespace_whitespace' => true,
            'no_short_bool_cast' => true,
            'no_trailing_comma_in_singleline_array' => true,
            'no_unreachable_default_argument_value' => true,
            'no_unused_imports' => true,
            'no_useless_else' => true,
            'no_useless_return' => true,
            'ordered_imports' => true,
            'php_unit_construct' => true,
            'php_unit_dedicate_assert' => true,
            'php_unit_fqcn_annotation' => true,
            'php_unit_strict' => false,
            'phpdoc_indent' => true,
            'phpdoc_no_access' => true,
            'phpdoc_no_alias_tag' => true,
            'phpdoc_no_package' => true,
            'phpdoc_scalar' => true,
            'phpdoc_separation' => true,
            'phpdoc_trim' => true,
            'phpdoc_types' => true,
            'pre_increment' => true,
            'psr4' => true,
            'self_accessor' => true,
            'semicolon_after_instruction' => true,
            'short_scalar_cast' => true,
            'single_blank_line_before_namespace' => true,
            'single_quote' => true,
            'space_after_semicolon' => true,
            'standardize_not_equals' => true,
            'strict_comparison' => true,
            'strict_param' => true,
            'ternary_operator_spaces' => true,
            'trailing_comma_in_multiline_array' => true,
            'trim_array_spaces' => true,
            'whitespace_after_comma_in_array' => true,
        ])
    ->setFinder(PhpCsFixer\Finder::create()
        ->in('examples')
        ->in('src')
        ->in('tests')
        ->exclude('JsonSchema')
    )
;
