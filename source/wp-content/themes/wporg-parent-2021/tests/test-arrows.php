<?php
/**
 * Tests for hiding arrow characters from screen readers.
 *
 * @package wporg-parent-2021
 */

declare( strict_types = 1 );

namespace WordPressdotorg\Theme\Parent_2021\Tests;

use function WordPressdotorg\Theme\Parent_2021\add_aria_hidden_to_arrows;

defined( 'ABSPATH' ) || die();

/**
 * Cover which arrows get wrapped: those in text nodes, never those in markup.
 */
class Test_Arrows extends \WP_UnitTestCase {

	/**
	 * Wrap arrows in text, and leave tags and unparsed elements byte-for-byte intact.
	 *
	 * @dataProvider data_content
	 *
	 * @param string $content  Content passed through the filter.
	 * @param string $expected Content the filter should return.
	 * @return void
	 */
	public function test_wraps_only_arrows_in_text( string $content, string $expected ): void {
		$this->assertSame( $expected, add_aria_hidden_to_arrows( $content ) );
	}

	/**
	 * Content and the expected filtered result.
	 *
	 * @return array<string, string[]>
	 */
	public function data_content(): array {
		$wrapped = '<span aria-hidden="true" class="wp-exclude-emoji">→</span>';

		return array(
			'arrow in text'                            => array(
				'<p>Next →</p>',
				'<p>Next ' . $wrapped . '</p>',
			),
			'arrow in attribute after unmatched quote' => array(
				'<img alt=O\'Brien title="Next →"><p>→</p>',
				'<img alt=O\'Brien title="Next →"><p>' . $wrapped . '</p>',
			),
			'arrow in and after a closed pre'          => array(
				'<pre>a → b</pre><p>→</p>',
				'<pre>a → b</pre><p>' . $wrapped . '</p>',
			),
			'arrow in select and svg'                  => array(
				'<select><option>→</option></select><svg><text>→</text></svg><p>→</p>',
				'<select><option>→</option></select><svg><text>→</text></svg><p>' . $wrapped . '</p>',
			),
			'arrow after an unclosed code'             => array(
				'<code>→',
				'<code>→',
			),
		);
	}
}
