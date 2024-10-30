<?php

/**
 * Plugin Name:       Button Downloads for S2Member
 * Plugin URI:        https://fd.dev.br/button-downloads-s2member/
 * Description:       Botão de download em posts para arquivos gerenciados pelo S2Member.
 * Version:           0.0.1
 * Requires at least: 5.7
 * Requires PHP:      7.2
 * Author:            Hellston Linhares
 * Author URI:        https://fd.dev.br/
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       s2buttondownloads
 * Domain Path:       /lang
 */
 
if (!function_exists('S2BUTTONDOWNLOADS_caminho_download_add_meta_box')) {

	function S2BUTTONDOWNLOADS_caminho_download_add_meta_box() {

		$screens = array( 'post' );

		foreach ( $screens as $screen ) {

			add_meta_box(
				'caminho_download_sectionid',
				__( 'Endereço do Arquivo para Download', 's2buttondownloads' ),
				'S2BUTTONDOWNLOADS_caminho_download_callback',
				$screen
			);
		}
	}
	
	add_action( 'add_meta_boxes', 'S2BUTTONDOWNLOADS_caminho_download_add_meta_box', 'advanced', 'high' );

}

if (!function_exists('S2BUTTONDOWNLOADS_caminho_download_callback')) {

	function S2BUTTONDOWNLOADS_caminho_download_callback( $post ) {

		wp_nonce_field( 'caminho_download', 'caminho_download_nonce' );

		$value = get_post_meta( $post->ID, 'caminho_download', true );

		echo '<label for="caminho_download">';
		_e( 'Informe o caminho completo dentro do Bucket do S3 da AWS / ou para a pasta s2member-files localizada dentro de wp-content/plugins', 's2buttondownloads' );
		echo '</label><br />';
		echo '<input type="text" id="caminho_download" name="caminho_download" value="' . esc_attr( $value ) . '" size="50" />';
	}

}

if (!function_exists('S2BUTTONDOWNLOADS_caminho_download_save_data')) {

	function S2BUTTONDOWNLOADS_caminho_download_save_data( $post_id ) {

		if ( ! isset( $_POST['caminho_download_nonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( $_POST['caminho_download_nonce'], 'caminho_download' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

			if ( ! current_user_can( 'edit_page', $post_id ) ) {
				return;
			}

		} else {

			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}
		}
		
		if ( ! isset( $_POST['caminho_download'] ) ) {
			return;
		}

		$my_data = sanitize_text_field( $_POST['caminho_download'] );

		update_post_meta( $post_id, 'caminho_download', $my_data );
	}

	add_action( 'save_post', 'S2BUTTONDOWNLOADS_caminho_download_save_data' );

}

//inserindo o botão de download
if (!function_exists('S2BUTTONDOWNLOADS_addContent')) {
	
	function S2BUTTONDOWNLOADS_addContent($content) {
		
		//verificando se o post tem caminho de download
		if (get_post_meta( get_the_ID(), 'caminho_download', true )) {
			$content .= '<center><a href="'.get_site_url().'?s2member_file_download='.get_post_meta( get_the_ID(), 'caminho_download', true ).'"><img title="'.__( 'Baixe o arquivo agora', 's2buttondownloads' ).'" src="'.get_site_url().'wp-content/plugins/button-downloads-s2member/download.png"></a></center>';
		}
		
		return $content; 
			
	}   

	add_filter('the_content', 'S2BUTTONDOWNLOADS_addContent');

}

