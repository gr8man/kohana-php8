<?php

declare(strict_types=1);
defined('SYSPATH') or die('Kohana bootstrap needs to be included before tests run');

/**
 * Tests for Kodoc_Class, Kodoc_Method, Kodoc_Property, Kodoc_Markdown
 *
 * @group kohana
 * @group kohana.userguide
 * @group kohana.userguide.kodoc
 * @package    Kohana/Userguide
 * @category   Tests
 */
class Kohana_KodocClassTest extends Unittest_TestCase
{
	public function test_kodoc_class_reflection(): void
	{
		$kodoc_class = new Kodoc_Class(Kodoc::class);
		$this->assertSame(Kodoc::class, $kodoc_class->class->getName());
		$this->assertIsArray($kodoc_class->constants);
		$this->assertIsArray($kodoc_class->tags);

		$methods = $kodoc_class->methods();
		$this->assertIsArray($methods);
		$this->assertNotEmpty($methods);

		$properties = $kodoc_class->properties();
		$this->assertIsArray($properties);
	}

	public function test_kodoc_method_reflection(): void
	{
		$kodoc_method = new Kodoc_Method(Kodoc::class, 'factory');
		$this->assertSame('factory', $kodoc_method->method->getName());
		$this->assertIsArray($kodoc_method->params);
		$this->assertNotEmpty($kodoc_method->source);
	}

	public function test_kodoc_property_reflection(): void
	{
		$kodoc_prop = new Kodoc_Property(Kodoc_Class::class, 'modifiers');
		$this->assertSame('modifiers', $kodoc_prop->property->getName());
		$this->assertNotEmpty($kodoc_prop->modifiers);
	}

	public function test_kodoc_markdown(): void
	{
		$markdown = "# Hello World\n\nThis is **bold** text.";
		$html = Kodoc_Markdown::markdown($markdown);
		$this->assertStringContainsString('<h1', $html);
		$this->assertStringContainsString('<strong>bold</strong>', $html);
	}
}
