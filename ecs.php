<?php

declare(strict_types=1);

use PHP_CodeSniffer\Standards\Generic\Sniffs\Arrays\DisallowLongArraySyntaxSniff;
use PhpCsFixer\Fixer\ArrayNotation\ArraySyntaxFixer;
use PhpCsFixer\Fixer\ClassNotation\OrderedClassElementsFixer;
use PhpCsFixer\Fixer\Comment\HeaderCommentFixer;
use PhpCsFixer\Fixer\FunctionNotation\PhpdocToPropertyTypeFixer;
use PhpCsFixer\Fixer\Import\FullyQualifiedStrictTypesFixer;
use PhpCsFixer\Fixer\Import\NoUnusedImportsFixer;
use PhpCsFixer\Fixer\Import\OrderedImportsFixer;
use PhpCsFixer\Fixer\LanguageConstruct\DeclareEqualNormalizeFixer;
use PhpCsFixer\Fixer\Phpdoc\PhpdocAlignFixer;
use PhpCsFixer\Fixer\PhpTag\BlankLineAfterOpeningTagFixer;
use PhpCsFixer\Fixer\PhpUnit\PhpUnitStrictFixer;
use PhpCsFixer\Fixer\Strict\DeclareStrictTypesFixer;
use PhpCsFixer\Fixer\Whitespace\ArrayIndentationFixer;
use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
                          __DIR__ . '/bundle',
                          __DIR__ . '/lib',
                          __DIR__ . '/tests',
                      ]);

    // Individual rules for more specific control
    $ecsConfig->rules([
                          NoUnusedImportsFixer::class,
                          DeclareStrictTypesFixer::class,
                          OrderedImportsFixer::class,
                          ArrayIndentationFixer::class,
                          OrderedClassElementsFixer::class,
                          DisallowLongArraySyntaxSniff::class,
                          FullyQualifiedStrictTypesFixer::class,
                          PhpdocToPropertyTypeFixer::class,
                      ]);

    $ecsConfig->ruleWithConfiguration(ArraySyntaxFixer::class, [
        'syntax' => 'short',
    ]);

    $ecsConfig->ruleWithConfiguration(HeaderCommentFixer::class, [
        'header' => "Content Additional Informations Bundle.\n\n@author    Florian ALEXANDRE\n@copyright 2023-present Florian ALEXANDRE\n@license   https://github.com/erdnaxelaweb/contentadditionalinformations/blob/main/LICENSE",
        'comment_type' => 'comment',
        'location' => 'after_declare_strict',
        'separate' => 'both',
    ]);

    // Rule sets
    $ecsConfig->sets([
                         SetList::CLEAN_CODE,
                         SetList::PSR_12,
                         SetList::ARRAY,
                         SetList::DOCBLOCK,
                         SetList::NAMESPACES,
                         SetList::COMMENTS,
                         SetList::STRICT,
                     ]);

    // Skip specific rules that might be too opinionated
    $ecsConfig->skip([
                         PhpdocAlignFixer::class => null,
                         PhpUnitStrictFixer::class => [
                             __DIR__ . '/tests/*',
                         ],
                     ]);
};
