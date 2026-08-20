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

		$constants = $kodoc_class->constants();
		$this->assertIsArray($constants);

		$desc = $kodoc_class->description();
		$this->assertIsString($desc);

		$tags = $kodoc_class->tags();
		$this->assertIsArray($tags);
	}

	public function test_kodoc_method_reflection(): void
	{
		$kodoc_method = new Kodoc_Method(Kodoc::class, 'source');
		$this->assertSame('source', $kodoc_method->method->getName());
		$this->assertIsArray($kodoc_method->params);
		$this->assertNotEmpty($kodoc_method->source);
		$this->assertNotEmpty($kodoc_method->params_short());
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

	public function test_kodoc_markdown_urls_and_notes(): void
	{
		$parser = new Kodoc_Markdown();
		Kodoc_Markdown::$base_url = 'http://example.com/base/';
		Kodoc_Markdown::$image_url = 'http://example.com/images/';

		$link = $parser->doBaseURL('[guide](guide/overview)');
		$this->assertStringContainsString('http://example.com/base/guide/overview', $link);

		$img = $parser->doImageURL('![logo](logo.png)');
		$this->assertStringContainsString('http://example.com/images/logo.png', $img);

		$note = $parser->doNotes("[!!] Important note\n\nNext paragraph");
		$this->assertIsString($note);

		$id = $parser->make_heading_id('Test Heading');
		$this->assertSame('test-heading', $id);

		$transformed = Kodoc_Markdown::markdown("# Main Title\n\nSome paragraph text.");
		$this->assertStringContainsString('Main Title', $transformed);
		$this->assertStringContainsString('Some paragraph text.', $transformed);

		Kodoc_Markdown::$base_url = 'http://example.com/';
		$with_base = $parser->doBaseURL('[guide](guide/index)');
		$this->assertStringContainsString('http://example.com/guide/index', $with_base);

		Kodoc_Markdown::$image_url = 'http://example.com/images/';
		$with_img = $parser->doImageURL('![logo](logo.png)');
		$this->assertStringContainsString('http://example.com/images/logo.png', $with_img);

		$with_api = $parser->doAPI('[Route::set]');
		$this->assertIsString($with_api);

		$with_note = $parser->doNotes("[!!] This is a note\n\n");
		$this->assertIsString($with_note);

		$with_views = $parser->doIncludeViews('Some text {{userguide/api/class}}');
		$this->assertIsString($with_views);
	}

	public function test_kodoc_factory_and_modules(): void
	{
		$kodoc = Kodoc::factory(Kodoc::class);
		$this->assertInstanceOf(Kodoc_Class::class, $kodoc);

		$show = Kodoc::show_class($kodoc);
		$this->assertIsBool($show);

		$transparent = Kodoc::is_transparent(Kodoc::class, array(Kodoc::class));
		$this->assertIsBool($transparent);
	}

	public function test_kodoc_format_tag_and_classes(): void
	{
		$tag_license = Kodoc::format_tag('license', 'http://example.com/license');
		$this->assertStringContainsString('http://example.com/license', $tag_license);

		$tag_link = Kodoc::format_tag('link', 'http://example.com Test Link');
		$this->assertStringContainsString('Test Link', $tag_link);

		$tag_throws = Kodoc::format_tag('throws', 'Kohana_Exception');
		$this->assertIsString($tag_throws);

		$tag_uses = Kodoc::format_tag('uses', 'Route::get');
		$this->assertIsString($tag_uses);

		$files = array(
			'classes/Kohana/Kodoc.php' => '/path/to/classes/Kohana/Kodoc.php',
		);
		$classes = Kodoc::classes($files);
		$this->assertArrayHasKey('Kohana_Kodoc', $classes);
	}
}
