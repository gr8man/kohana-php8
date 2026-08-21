<?php

declare(strict_types=1);

/**
 * Standard Markdown and MarkdownExtra parser for Kohana Userguide.
 */
class Markdown_Parser
{
	public array $span_gamut = [];
	public array $document_gamut = [];

	public function __construct()
	{
		$this->span_gamut = [
			'parseSpan' => 50,
		];
		$this->document_gamut = [
			'parseBlocks' => 50,
		];
	}

	public function transform(string $text): string
	{
		// Process document-level gamuts
		asort($this->document_gamut);
		foreach ($this->document_gamut as $method => $priority) {
			if (method_exists($this, $method)) {
				$text = $this->$method($text);
			}
		}

		// Standard paragraphs & linebreaks
		$paragraphs = preg_split('/\n{2,}/', trim($text));
		$output = [];
		foreach ($paragraphs as $para) {
			if (trim($para) === '') {
				continue;
			}
			if (preg_match('/^<(h[1-6]|div|p|ul|ol|li|blockquote|pre|code|table)/i', trim($para))) {
				$output[] = trim($para);
			} elseif (preg_match('/^(#{1,6})\s+(.+)$/s', trim($para), $hMatches)) {
				$level = strlen($hMatches[1]);
				$output[] = "<h{$level}>" . $this->runSpanGamut(trim($hMatches[2])) . "</h{$level}>";
			} elseif (preg_match('/^( {4}|\t)(.*)$/m', $para)) {
				$lines = explode("\n", $para);
				$codeLines = [];
				foreach ($lines as $line) {
					$codeLines[] = preg_replace('/^( {4}|\t)/', '', $line);
				}
				$output[] = "<pre><code>" . htmlspecialchars(implode("\n", $codeLines), ENT_NOQUOTES, 'UTF-8') . "\n</code></pre>";
			} else {
				$output[] = '<p>' . $this->runSpanGamut(trim($para)) . '</p>';
			}
		}

		return implode("\n\n", $output) . "\n";
	}

	public function parseBlocks(string $text): string
	{
		return $text;
	}

	public function runSpanGamut(string $text): string
	{
		asort($this->span_gamut);
		foreach ($this->span_gamut as $method => $priority) {
			if (method_exists($this, $method)) {
				$text = $this->$method($text);
			}
		}
		return $text;
	}

	public function parseSpan(string $text): string
	{
		// Inline code: `code`
		$text = preg_replace('/`([^`]+)`/', '<code>$1</code>', $text);
		// Bold: **bold**
		$text = preg_replace('/\*\*([^*]+)\*\*/', '<strong>$1</strong>', $text);
		// Italic: *italic*
		$text = preg_replace('/\*([^*]+)\*/', '<em>$1</em>', $text);
		// Links: [text](url)
		$text = preg_replace('/\[([^\]]+)\]\(([^)]+)\)/', '<a href="$2">$1</a>', $text);
		return $text;
	}

	public function hashBlock(string $text): string
	{
		return $text;
	}
}

class MarkdownExtra_Parser extends Markdown_Parser
{
	public function __construct()
	{
		parent::__construct();
	}

	public function _doHeaders_attr(?string &$id): string
	{
		if ($id === null || trim($id) === '') {
			return '';
		}
		$id = trim($id, ' #{}');
		return ' id="' . htmlspecialchars($id, ENT_QUOTES, 'UTF-8') . '"';
	}
}

if (!function_exists('Markdown')) {
	function Markdown(string $text): string
	{
		$parser = new Markdown_Parser();
		return $parser->transform($text);
	}
}
