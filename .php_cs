<?php

return Symfony\CS\Config\Config::create()
    ->fixers(
        [
            '-concat_without_spaces',
            '-empty_return',
            '-phpdoc_no_empty_return',
            '-phpdoc_params',
            '-phpdoc_to_comment',
            '-single_array_no_trailing_comma',
            '-unneeded_control_parentheses',
            'concat_with_spaces',
            'ereg_to_preg',
            'ordered_use',
            'short_array_syntax',
        ]
    )
    ->finder(
        Symfony\CS\Finder\DefaultFinder::create()
            ->in('examples')
            ->in('src')
            ->in('tests')
            ->exclude('JsonSchema')
    )
    ;
