<?php
/**
 * Active callback functions for the customizer
 */

function ocn_cac_has_close_target() {
	if ( 'close' == get_theme_mod( 'ocn_target', 'button' ) ) {
		return true;
	} else {
		return false;
	}
}

function ocn_cac_has_btn_target() {
	if ( 'button' == get_theme_mod( 'ocn_target', 'button' ) ) {
		return true;
	} else {
		return false;
	}
}