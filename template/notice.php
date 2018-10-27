<?php
/**
 * Cookie notice
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Vars
$content 		= get_theme_mod( 'ocn_content', 'By continuing to use this website, you consent to the use of cookies in accordance with our Cookie Policy.' );
$button_text 	= get_theme_mod( 'ocn_button_text', 'Accept' );

// Translate theme mods
$content 		= oceanwp_tm_translation( 'ocn_content', $content );
$button_text 	= oceanwp_tm_translation( 'ocn_button_text', $button_text );

// Style
$style = get_theme_mod( 'ocn_style', 'flyin' );
$style = $style ? $style : 'flyin';

// Close target
$target = get_theme_mod( 'ocn_target', 'button' );
$target = $target ? $target : 'button';

// Overlay
$overlay = get_theme_mod( 'ocn_overlay', 'no' );
$overlay = $overlay ? $overlay : 'no'; ?>

<?php
// If overlay
if ( 'yes' == $overlay ) { ?>
	<div id="ocn-cookie-overlay"></div>
<?php
} ?>
	
<div id="ocn-cookie-wrap" class="<?php echo esc_attr( $style ); ?>">

	<?php
	// If close icon
	if ( 'close' == $target ) { ?>
		<a class="ocn-icon ocn-close" href="#">
			<svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 512 512" xml:space="preserve">
				<g>
					<g>
						<path d="M505.943,6.058c-8.077-8.077-21.172-8.077-29.249,0L6.058,476.693c-8.077,8.077-8.077,21.172,0,29.249
							C10.096,509.982,15.39,512,20.683,512c5.293,0,10.586-2.019,14.625-6.059L505.943,35.306
							C514.019,27.23,514.019,14.135,505.943,6.058z"/>
					</g>
				</g>
				<g>
					<g>
						<path d="M505.942,476.694L35.306,6.059c-8.076-8.077-21.172-8.077-29.248,0c-8.077,8.076-8.077,21.171,0,29.248l470.636,470.636
							c4.038,4.039,9.332,6.058,14.625,6.058c5.293,0,10.587-2.019,14.624-6.057C514.018,497.866,514.018,484.771,505.942,476.694z"/>
					</g>
				</g>
			</svg>
		</a>
	<?php
	} ?>

	<div id="ocn-cookie-inner">

		<p class="ocn-cookie-content"><?php echo do_shortcode( $content ); ?></p>

		<?php
		// If button
		if ( 'button' == $target ) { ?>
			<a class="button ocn-btn ocn-close" href="#"><?php echo esc_html( $button_text ); ?></a>
		<?php
		} ?>

	</div><!-- #ocn-cookie-inner -->

</div><!-- #ocn-cookie-wrap -->