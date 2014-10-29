<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Meta Box Video
*
* diplays text input for oembed video
*
* @version 1.0
* @author codeBOX
* @project lifterLMS
*/
class LLMS_Meta_Box_Access {

	/**
	 * Set up video input
	 *
	 * @return string
	 * @param string $post
	 */
	public static function output( $post ) {
		global $post;
		wp_nonce_field( 'lifterlms_save_data', 'lifterlms_meta_nonce' );

		$is_restricted = get_post_meta( $post->ID, '_llms_is_restricted', true );
		$required_membership_levels = get_post_meta( $post->ID, '_llms_restricted_levels', true );

		$membership_levels_args = array(
			'posts_per_page'   => -1,
			'post_status'      => 'publish',
			'orderby'          => 'title',
			'order'            => 'ASC',
			'post_type'        => 'llms_membership',
			'suppress_filters' => true 
		); 
		$membership_levels = get_posts($membership_levels_args);
		?>

		<div id="llms-access-options">
			<div class="llms-access-option">
				<span>
					<input type="checkbox" name="_llms_is_restricted" <?php if( $is_restricted == true ) { ?>checked="checked"<?php } ?> />
				</span>
				<?php
				$label  = '';
				$label .= '<label for="_llms_is_restricted">' . __( 'Restrict this content', 'lifterlms' ) . '</label> ';
				echo $label;
				?>
			</div>

			<div class="llms-access-levels">
			
				<span class="llms-access-levels-title"><?php _e( 'Membership Restrictions', 'lifterlms' ) ?></span> 
				<?php
						// create display grid. mark restricted courses as checked.
						if($membership_levels) : 

						foreach ( $membership_levels as $level  ) : 

							if( in_array($level->ID, $required_membership_levels) ) {
								$checked = 'checked ="checked"';
							}
							else {
								$checked = '';
							}
							echo '<div class="llms-access-level">';
							echo '<input type="checkbox" name="llms_level[]" ' . $checked . ' value="' . $level->ID . '"/>';
							echo '<label for="llms_level">' . $level->post_title . '</label> ';
							echo '</div>';
						endforeach; 

					endif;
					?>
			</div>
		</div>

		<?php  
	}

	public static function save( $post_id, $post ) {
		global $wpdb;

		$membership_levels = array();

		foreach( $_POST['llms_level'] as $value ) {

			array_push($membership_levels, $value);
		}

		if ( ! empty($membership_levels) ) {

			$is_restricted = ( llms_clean( $_POST['_llms_is_restricted']  ) );
			update_post_meta( $post_id, '_llms_is_restricted', ( $is_restricted === '' ) ? '' : $is_restricted );
 
		}
		else {
			$empty = '';
			update_post_meta( $post_id, '_llms_is_restricted', $empty );
		}

		if ( $_POST['_llms_is_restricted'] ) {
			update_post_meta( $post_id, '_llms_restricted_levels', ( $membership_levels === '' ) ? '' : $membership_levels);
		}
		else {
			$empty = array();
			update_post_meta( $post_id, '_llms_restricted_levels', $empty );
		}
	}

}