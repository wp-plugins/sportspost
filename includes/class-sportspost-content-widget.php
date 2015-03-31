<?php

/**
 * Widget for Sports Content.
 *
 * @package    SportsPost
 * @subpackage SportsPost/includes
 * @since   1.2.0
 */
class SportsPost_Content_Widget extends WP_Widget {
	
	/**
	 * Initialize the Widget.
	 *
	 * @since 1.2.0
	 */
	public function __construct() {
		$widget_ops = array( 'classname' => 'widget_sportspost_content', 'description' => __( 'Scores, stats, standings, and more - all provided by the XML Team Solutions, the makers of the SportsPost plugin.', 'sportspost' ) );
		$control_ops = array( 'width' => 400, 'height' => 350 );
		parent::__construct( 'sportspost_content', __('SportsPost Live Content', 'sportspost' ), $widget_ops, $control_ops );
	}

	/**
	 * Echo the widget content.
	 *
	 * @since 1.2.0
	 * @param array $args     Display arguments including before_title, after_title,
	 *                        before_widget, and after_widget.
	 * @param array $instance The settings for the particular instance of the widget.
	 */
	public function widget( $args, $instance ) {
		// Retrieve content
		$title = apply_filters( 'widget_title', empty( $instance['title'] ) ? '' : $instance['title'], $instance, $this->id_base );
		$plugin = new SportsPost();
		$plugin_public = new SportsPost_Public( $plugin->get_plugin_name(), $plugin->get_version() );
		$text = $plugin_public->render_content_url( $instance['url'] );
		// Display widget
		echo $args['before_widget'];
		if ( ! empty( $title ) ) {
			echo $args['before_title'] . $title . $args['after_title'];
		} ?>
			<div class="sportspost_content_widget"><?php echo ! empty( $text ) ? $text : ''; ?></div>
		<?php
		echo $args['after_widget'];
	}

	/**
	 * Update a particular instance.
	 *
	 * @since 1.2.0
	 *
	 * @param array $new_instance New settings for this instance as input by the user via
	 * @param array $old_instance Old settings for this instance.
	 * @return array Settings to save or bool false to cancel saving.
	 */
	public function update( $new_instance, $old_instance ) {
		$instance = $old_instance;
		$instance['title'] = strip_tags( $new_instance['title'] );
		$instance['url'] = strip_tags( $new_instance['url'] );
		return $instance;
	}

	/**
	 * Output the Widget settings update form.
	 *
	 * @since 1.2.0
	 *
	 * @param array $instance Current settings.
	 * @return string Default return is 'noform'.
	 */
	public function form( $instance ) {
		$instance = wp_parse_args( (array) $instance, array( 'title' => '', 'url' => '' ) );
		$title = strip_tags( $instance['title'] );
		$url = esc_textarea( $instance['url'] );
?>
		<p><?php echo sprintf( __( 'Add a URL for a widget containing sports content. Visit <a target="_blank" href="%s">plugin FAQ</a> for details.' ), 'https://wordpress.org/plugins/sportspost/faq/' ); ?></p>

		<p><label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:', 'sportspost' ); ?></label>
		<input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo esc_attr($title); ?>" /></p>

		<p><label for="<?php echo $this->get_field_id( 'url' ); ?>"><?php _e( 'URL:', 'sportspost' ); ?></label>
		<textarea class="widefat" rows="16" cols="20" id="<?php echo $this->get_field_id( 'url' ); ?>" name="<?php echo $this->get_field_name('url'); ?>"><?php echo $url; ?></textarea></p>
<?php
	}
}