<?php // phpcs:disable WordPress.Files.FileName.InvalidClassFileName -- PHPUnit discovers these files by their `test-` prefix.
/**
 * Tests for expanding shortcodes authored into a pattern.
 *
 * @package wporg-parent-2021
 */

declare( strict_types = 1 );

namespace WordPressdotorg\Theme\Parent_2021\Tests;

use WP_UnitTestCase;

defined( 'ABSPATH' ) || die();

/**
 * Cover the boundary between a pattern's own source markup, whose shortcodes
 * are expanded, and the values its blocks render, whose shortcodes are not.
 */
class Test_Pattern_Shortcodes extends WP_UnitTestCase {

	/**
	 * How many times the `[count]` shortcode has run in the current test.
	 *
	 * @var int
	 */
	protected int $count_calls = 0;

	/**
	 * Slugs registered by render_pattern(), to unregister afterwards.
	 *
	 * @var string[]
	 */
	protected array $registered = array();

	/**
	 * Register the shortcodes the tests render.
	 *
	 * @return void
	 */
	public function set_up(): void {
		parent::set_up();

		$this->count_calls = 0;

		add_shortcode(
			'count',
			function (): string {
				++$this->count_calls;

				return 'COUNTED';
			}
		);

		add_shortcode(
			'wrap',
			function ( $atts, string $content = '' ): string {
				return '<em>' . $content . '</em>';
			}
		);
	}

	/**
	 * Remove only what this test registered.
	 *
	 * @return void
	 */
	public function tear_down(): void {
		remove_shortcode( 'count' );
		remove_shortcode( 'wrap' );

		foreach ( $this->registered as $slug ) {
			unregister_block_pattern( $slug );
		}
		$this->registered = array();

		parent::tear_down();
	}

	/**
	 * Register $content as a pattern and return the rendered `core/pattern` block.
	 *
	 * @param string $content Block markup for the pattern.
	 *
	 * @return string The rendered output.
	 */
	protected function render_pattern( string $content ): string {
		$slug               = 'test/pattern-' . count( $this->registered );
		$this->registered[] = $slug;

		register_block_pattern(
			$slug,
			array(
				'title'   => 'Test',
				'content' => $content,
			)
		);

		return do_blocks( sprintf( '<!-- wp:pattern {"slug":"%s"} /-->', $slug ) );
	}

	/**
	 * Put a post in the loop so post blocks have something to render.
	 *
	 * @param array $args Arguments for wp_insert_post().
	 *
	 * @return void
	 */
	protected function set_up_post( array $args ): void {
		$post_id = self::factory()->post->create( $args );

		$this->go_to( (string) get_permalink( $post_id ) );
		$GLOBALS['wp_query']->the_post();
	}

	/**
	 * A shortcode written into a pattern is expanded.
	 *
	 * @return void
	 */
	public function test_expands_shortcode_in_pattern_source(): void {
		$output = $this->render_pattern( '<!-- wp:paragraph --><p>[count]</p><!-- /wp:paragraph -->' );

		$this->assertStringContainsString( 'COUNTED', $output );
	}

	/**
	 * Blocks nested inside the pattern are covered too.
	 *
	 * @return void
	 */
	public function test_expands_shortcode_nested_in_group(): void {
		$output = $this->render_pattern(
			'<!-- wp:group --><div class="wp-block-group">'
			. '<!-- wp:paragraph --><p>[count]</p><!-- /wp:paragraph -->'
			. '</div><!-- /wp:group -->'
		);

		$this->assertStringContainsString( 'COUNTED', $output );
	}

	/**
	 * An enclosing shortcode inside a single block is expanded.
	 *
	 * @return void
	 */
	public function test_expands_enclosing_shortcode_within_one_block(): void {
		$output = $this->render_pattern( '<!-- wp:shortcode -->[wrap]hello[/wrap]<!-- /wp:shortcode -->' );

		$this->assertStringContainsString( '<em>hello</em>', $output );
	}

	/**
	 * An enclosing shortcode wrapping sibling blocks is expanded from the pattern's
	 * markup, before it is split into blocks.
	 *
	 * @return void
	 */
	public function test_expands_enclosing_shortcode_across_blocks(): void {
		$output = $this->render_pattern(
			'<!-- wp:group --><div class="wp-block-group">[wrap]'
			. '<!-- wp:paragraph --><p>Hi</p><!-- /wp:paragraph -->'
			. '[/wrap]</div><!-- /wp:group -->'
		);

		$this->assertStringContainsString( '<em>', $output );
		$this->assertStringNotContainsString( '[wrap]', $output );
		$this->assertStringNotContainsString( '[/wrap]', $output );
	}

	/**
	 * Core renders the inner blocks of `core/navigation` without `render_block_data`,
	 * which the pass over the pattern's markup does not depend on.
	 *
	 * @return void
	 */
	public function test_expands_shortcode_below_a_navigation_block(): void {
		$output = $this->render_pattern(
			'<!-- wp:navigation {"overlayMenu":"never"} -->'
			. '<!-- wp:paragraph --><p>[count]</p><!-- /wp:paragraph -->'
			. '<!-- /wp:navigation -->'
		);

		$this->assertStringContainsString( 'COUNTED', $output );
		$this->assertSame( 1, $this->count_calls );
	}

	/**
	 * Each shortcode runs once. `innerHTML` repeats `innerContent`, so expanding
	 * both would run every shortcode callback twice.
	 *
	 * @return void
	 */
	public function test_runs_each_shortcode_exactly_once(): void {
		$this->render_pattern( '<!-- wp:paragraph --><p>[count]</p><!-- /wp:paragraph -->' );

		$this->assertSame( 1, $this->count_calls );
	}

	/**
	 * A shortcode in a post title is never handed to the parser.
	 *
	 * @return void
	 */
	public function test_does_not_parse_shortcode_in_post_title(): void {
		$this->set_up_post( array( 'post_title' => 'Title [count]' ) );

		$output = $this->render_pattern( '<!-- wp:post-title /-->' );

		$this->assertStringContainsString( '[count]', $output );
		$this->assertStringNotContainsString( 'COUNTED', $output );
		$this->assertSame( 0, $this->count_calls );
	}

	/**
	 * The same holds for the excerpt, which `the_excerpt` never expands.
	 *
	 * @return void
	 */
	public function test_does_not_parse_shortcode_in_post_excerpt(): void {
		$this->set_up_post(
			array(
				'post_title'   => 'Title',
				'post_excerpt' => 'Excerpt [count]',
			)
		);

		$output = $this->render_pattern( '<!-- wp:post-excerpt /-->' );

		$this->assertStringContainsString( '[count]', $output );
		$this->assertSame( 0, $this->count_calls );
	}

	/**
	 * Post content is unaffected: `the_content` expands its shortcodes itself.
	 *
	 * @return void
	 */
	public function test_post_content_shortcodes_still_expand(): void {
		$this->set_up_post(
			array(
				'post_title'   => 'Title',
				'post_content' => '<!-- wp:paragraph --><p>[count]</p><!-- /wp:paragraph -->',
			)
		);

		$output = $this->render_pattern( '<!-- wp:post-content /-->' );

		$this->assertStringContainsString( 'COUNTED', $output );
	}

	/**
	 * A pattern reached through post content still expands its own shortcodes here,
	 * at `do_blocks()`, exactly as the previous output-level pass did. Deferring
	 * them to `the_content`'s pass at priority 11 would hand a pattern's rendered
	 * output — post titles included — back to the shortcode parser.
	 *
	 * @return void
	 */
	public function test_pattern_inside_post_content_still_expands(): void {
		add_shortcode(
			'fmt',
			function (): string {
				return 'A "quoted" phrase';
			}
		);

		$slug               = 'test/pattern-in-content';
		$this->registered[] = $slug;
		register_block_pattern(
			$slug,
			array(
				'title'   => 'In content',
				'content' => '<!-- wp:paragraph --><p>[fmt]</p><!-- /wp:paragraph -->',
			)
		);

		$output = apply_filters( 'the_content', sprintf( '<!-- wp:pattern {"slug":"%s"} /-->', $slug ) );

		remove_shortcode( 'fmt' );

		$this->assertStringNotContainsString( '[fmt]', $output );
		// Expanded before wptexturize, which is where the previous pass ran too.
		$this->assertStringContainsString( '&#8220;quoted&#8221;', $output );
	}

	/**
	 * Nesting `core/post-content` in a pattern must not change when its shortcodes
	 * run. Expanding them at `do_blocks` rather than leaving them to `the_content`
	 * would push their output through wpautop and wptexturize as well.
	 *
	 * @return void
	 */
	public function test_nested_post_content_is_not_texturized_twice(): void {
		add_shortcode(
			'quoted',
			function (): string {
				return 'A "quoted" phrase';
			}
		);

		$this->set_up_post(
			array(
				'post_title'   => 'Title',
				'post_content' => '<!-- wp:paragraph --><p>[quoted]</p><!-- /wp:paragraph -->',
			)
		);

		$via_pattern = $this->render_pattern( '<!-- wp:post-content /-->' );
		$via_content = apply_filters( 'the_content', get_post_field( 'post_content', get_the_ID() ) );

		remove_shortcode( 'quoted' );

		$this->assertStringContainsString( 'A "quoted" phrase', $via_pattern );
		$this->assertStringNotContainsString( '&#8220;', $via_pattern );
		$this->assertSame( wp_strip_all_tags( $via_content ), wp_strip_all_tags( $via_pattern ) );
	}

	/**
	 * Regression test for the reported stored XSS.
	 *
	 * The title holds no `<`, `>`, `"` or `'`, so it survives `sanitize_text_field()`
	 * and `esc_html()` byte-identical. Expanding it would run `stripcslashes()` over
	 * the shortcode's attributes and emit the escapes as raw markup.
	 *
	 * @return void
	 */
	public function test_escaped_title_cannot_reintroduce_markup(): void {
		$payload = 'H [caption width=1 caption=\x3ca\x20href=\x22javascript:alert(1)\x22\x3ePWN\x3c/a\x3e]CAP[/caption] END';

		// Control: the payload survives escaping untouched, and is markup once a parser sees it.
		$this->assertSame( $payload, esc_html( $payload ) );
		$this->assertStringContainsString( '<a ', do_shortcode( esc_html( $payload ) ) );

		$this->set_up_post( array( 'post_title' => wp_slash( $payload ) ) );

		$output = $this->render_pattern( '<!-- wp:post-title /-->' );

		$this->assertStringContainsString( '[caption', $output );
		$this->assertStringNotContainsString( 'wp-caption-text', $output );
		$this->assertStringNotContainsString( '<a ', $output );
	}

	/**
	 * Core autoembeds inside its own pattern callback, which this theme replaces, so
	 * the replacement has to keep doing it.
	 *
	 * @return void
	 */
	public function test_embeds_still_work_in_patterns(): void {
		$callback = function (): string {
			return '<em>EMBEDDED</em>';
		};
		add_filter( 'pre_oembed_result', $callback );

		$output = $this->render_pattern( '<!-- wp:paragraph --><p>https://example.test/v/1</p><!-- /wp:paragraph -->' );

		remove_filter( 'pre_oembed_result', $callback );

		$this->assertStringContainsString( 'EMBEDDED', $output );
	}

	/**
	 * Blocks rendered outside any pattern keep their shortcodes untouched, so
	 * `the_content` remains the only thing expanding post content.
	 *
	 * @return void
	 */
	public function test_leaves_blocks_outside_a_pattern_alone(): void {
		$output = do_blocks( '<!-- wp:paragraph --><p>[count]</p><!-- /wp:paragraph -->' );

		$this->assertStringContainsString( '[count]', $output );
		$this->assertSame( 0, $this->count_calls );
	}
}
