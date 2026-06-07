<?php

namespace SmartSearch\Tests\Unit;

use SmartSearch\Support\ArabicNormalizer;
use SmartSearch\Tests\TestCase;

class ArabicNormalizerTest extends TestCase
{
    public function test_normalize_removes_diacritics(): void
    {
        $result = ArabicNormalizer::normalize('مُحَمَّد');
        $this->assertEquals('محمد', $result);
    }

    public function test_normalize_unifies_alef_forms(): void
    {
        $result = ArabicNormalizer::normalize('أحمد إبراهيم آدم');
        $this->assertEquals('احمد ابراهيم ادم', $result);
    }

    public function test_normalize_ta_marbuta(): void
    {
        $result = ArabicNormalizer::normalize('مدرسة');
        $this->assertEquals('مدرسه', $result);
    }

    public function test_normalize_alif_maqsura(): void
    {
        $result = ArabicNormalizer::normalize('مستشفى');
        $this->assertEquals('مستشفي', $result);
    }

    public function test_normalize_array(): void
    {
        $result = ArabicNormalizer::normalizeArray(['مُحَمَّد', 'أحمد']);
        $this->assertEquals(['محمد', 'احمد'], $result);
    }

    public function test_normalize_handles_empty_string(): void
    {
        $this->assertEquals('', ArabicNormalizer::normalize(''));
    }

    public function test_normalize_handles_latin_text(): void
    {
        $this->assertEquals('hello world', ArabicNormalizer::normalize('hello world'));
    }
}
