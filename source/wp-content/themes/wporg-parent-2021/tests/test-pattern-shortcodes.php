<?php // phpcs:disable WordPress.Files.FileName.InvalidClassFileName -- PHPUnit discovers these files by their `test-` prefix.
/**
 * Tests for expanding shortcodes authored into a pattern.
 *
 * @package wporg-parent-2021
 */

declare( strict_types = 1 );

namespace WordPressdotorg\Theme\Parent_2021\Tests;

use WP_UnitTestCase;
use function WordPressdotorg\Theme\Parent_2021\Pattern_Shortcodes\do_pattern_source_shortcodes;
use function WordPressdotorg\Theme\Parent_2021\Pattern_Shortcodes\pattern_render_depth;
use function WordPressdotorg\Theme\Parent_2021\Pattern_Shortcodes\restore_render_depth;
use const WordPressdotorg\Theme\Parent_2021\Pattern_Shortcodes\NESTED_CONTENT_BLOCKS;

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

		// A render that failed part way leaves the depth raised; drain it so the next test starts clean.
		while ( pattern_render_depth() ) {
			pattern_render_depth( -1 );
		}

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
	 * `core/shortcode` wraps its own markup in `wpautop()`, so expanding before it
	 * runs would push the shortcode's output through that too.
	 *
	 * @return void
	 */
	public function test_shortcode_block_output_is_not_autop_mangled(): void {
		add_shortcode(
			'multi',
			function (): string {
				return "<span>one</span>\n<span>two</span>";
			}
		);

		$output = $this->render_pattern( '<!-- wp:shortcode -->[multi]<!-- /wp:shortcode -->' );

		remove_shortcode( 'multi' );

		$this->assertStringContainsString( '<span>one</span>', $output );
		$this->assertStringNotContainsString( '<br', $output );
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
	 * A filter earlier on the hook may hand along a null; typing it away would
	 * turn that into a fatal.
	 *
	 * @return void
	 */
	public function test_survives_a_null_from_an_earlier_filter(): void {
		$this->assertNull( restore_render_depth( null, array() ) );
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
	 * Every core block that runs a shortcode pass before `do_blocks()` has to be
	 * listed. The test below iterates the list, so it cannot notice a missing entry.
	 *
	 * `core/latest-posts` and `core/template-part` go through
	 * `_wp_apply_block_content_filters()`, which expands and then parses blocks;
	 * `core/post-content` leaves its shortcodes to `the_content` at priority 11.
	 *
	 * @return void
	 */
	public function test_pre_expanding_core_blocks_are_listed(): void {
		$expected = array(
			'core/latest-posts',
			'core/post-content',
			'core/template-part',
		);

		foreach ( $expected as $block_name ) {
			$this->assertContains( $block_name, NESTED_CONTENT_BLOCKS, "{$block_name} runs its own shortcode pass." );
		}
	}

	/**
	 * Blocks that run their own shortcode pass over content they then hand to
	 * `do_blocks()` must not have their inner blocks expanded as well.
	 *
	 * @return void
	 */
	public function test_suspends_below_blocks_that_pre_expand(): void {
		// A shortcode whose output is itself shortcode syntax, which a second pass would expand.
		add_shortcode(
			'emit',
			function (): string {
				return '[count]';
			}
		);

		foreach ( NESTED_CONTENT_BLOCKS as $block_name ) {
			$pattern = do_pattern_source_shortcodes( array( 'blockName' => 'core/pattern' ) );
			$this->assertSame( 1, pattern_render_depth() );

			$nested = do_pattern_source_shortcodes( array( 'blockName' => $block_name ) );
			$this->assertSame( 0, pattern_render_depth(), "Depth is suspended below {$block_name}." );

			$inner = do_pattern_source_shortcodes(
				array(
					'blockName'    => 'core/paragraph',
					'innerContent' => array( '<p>[count] [emit]</p>' ),
				)
			);
			$this->assertSame( array( '<p>[count] [emit]</p>' ), $inner['innerContent'], "Content below {$block_name} is left alone." );
			$this->assertSame( 0, $this->count_calls );

			restore_render_depth( '', $nested );
			$this->assertSame( 1, pattern_render_depth(), 'The pattern depth comes back.' );

			restore_render_depth( '', $pattern );
			$this->assertSame( 0, pattern_render_depth() );
		}

		remove_shortcode( 'emit' );
	}

	/**
	 * A nested pattern restores the outer pattern's depth when it finishes, so
	 * blocks after it are still expanded.
	 *
	 * @return void
	 */
	public function test_nested_pattern_keeps_outer_depth(): void {
		$inner_slug         = 'test/pattern-inner';
		$this->registered[] = $inner_slug;
		register_block_pattern(
			$inner_slug,
			array(
				'title'   => 'Inner',
				'content' => '<!-- wp:paragraph --><p>INNER [count]</p><!-- /wp:paragraph -->',
			)
		);

		$output = $this->render_pattern(
			'<!-- wp:paragraph --><p>BEFORE [count]</p><!-- /wp:paragraph -->'
			. sprintf( '<!-- wp:pattern {"slug":"%s"} /-->', $inner_slug )
			. '<!-- wp:paragraph --><p>AFTER [count]</p><!-- /wp:paragraph -->'
		);

		$this->assertStringContainsString( 'BEFORE COUNTED', $output );
		$this->assertStringContainsString( 'INNER COUNTED', $output );
		$this->assertStringContainsString( 'AFTER COUNTED', $output );
	}

	/**
	 * Core renders some inner blocks without `render_block_data`. A pattern
	 * reached that way finishes without having started, and must not consume the
	 * depth belonging to the pattern around it.
	 *
	 * @return void
	 */
	public function test_unstarted_pattern_does_not_consume_depth(): void {
		$stray = array(
			'blockName'    => 'core/pattern',
			'attrs'        => array( 'slug' => 'test/absent' ),
			'innerBlocks'  => array(),
			'innerHTML'    => '',
			'innerContent' => array(),
		);

		$callback = function ( string $content ) use ( $stray ): string {
			// phpcs:ignore WordPress.NamingConventions.ValidHookName.UseUnderscores -- Core's block-specific hook name.
			return $content . apply_filters( 'render_block_core/pattern', '', $stray, null );
		};
		add_filter( 'render_block_core/html', $callback );

		$output = $this->render_pattern(
			'<!-- wp:paragraph --><p>ONE [count]</p><!-- /wp:paragraph -->'
			. '<!-- wp:html --><span></span><!-- /wp:html -->'
			. '<!-- wp:paragraph --><p>TWO [count]</p><!-- /wp:paragraph -->'
		);

		remove_filter( 'render_block_core/html', $callback );

		$this->assertStringContainsString( 'ONE COUNTED', $output );
		$this->assertStringContainsString( 'TWO COUNTED', $output );
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

	/**
	 * Every render leaves the depth where it found it.
	 *
	 * @return void
	 */
	public function test_depth_is_balanced_after_rendering(): void {
		$this->assertSame( 0, pattern_render_depth() );

		$this->render_pattern( '<!-- wp:paragraph --><p>[count]</p><!-- /wp:paragraph -->' );

		$this->assertSame( 0, pattern_render_depth() );
	}
}
