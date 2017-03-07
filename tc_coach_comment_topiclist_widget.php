<?php
// Creating the widget 
class tc_coach_comment_topiclist_widget extends WP_Widget {

function __construct() {
parent::__construct(
// Base ID of your widget
'tc_coach_comment_topiclist_widget', 

// Widget name will appear in UI
__('Comment Topic Widget', 'tc_coach_comment_topiclist_widget'), 

// Widget description
array( 'description' => __( 'List the comment topics to open in an iframe for a post', 'tc_coach_comment_topiclist_widget' ), ) 
);
}
// Creating widget front-end
// This is where the action happens
public function widget( $args, $instance ) {
global $wpdb;
$title = apply_filters( 'widget_title', $instance['title'] );
$post_id = apply_filters( 'widget_post_id', $instance['post_id'] );
$outputLink=$args['before_title'] . $title . $args['after_title'];
//set up the output links
//get the comment topics associated witht this post
$rows=$wpdb->get_results($wpdb->prepare("Select post_id, post_title  from wp_postmeta m, wp_posts p where p.ID=m.post_id and  meta_key =%s and meta_value = %d",'associated_post',$post_id),OBJECT);
foreach ($rows as $row){
$outputLink.="<a class='fancybox-iframe' href='/?p=". $row->post_id."'>". $row->post_title."</a><br>";	
}
// before and after widget arguments are defined by themes
echo $args['before_widget'];
// This is where you run the code and display the output
//get the coach id and current user id
echo __(  $outputLink , 'tc_coach_comment_topiclist_widget' );
echo $args['after_widget'];
}
		
// Widget Backend 
public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( 'New title', 'tc_coach_comment_topiclist_widget' );
}
if ( isset( $instance[ 'post_id' ] ) ) {
$post_id = $instance[ 'post_id' ];
}
else {
$post_id = __( '0', 'tc_coach_comment_topiclist_widget' );
}
// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
<label for="<?php echo $this->get_field_id( 'course_id' ); ?>"><?php _e( 'Post ID that relates to the topics:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'post_id' ); ?>" name="<?php echo $this->get_field_name( 'post_id'); ?>" type="text" value="<?php echo esc_attr( $post_id ); ?>" />
</p>
<?php 
}
	
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
$instance = array();
$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
$instance['post_id'] = ( ! empty( $new_instance['post_id'] ) ) ? strip_tags( $new_instance['post_id'] ) : '';
return $instance;
}
} // tc_coach_comment_topiclist_widget class ends here

// Register and load the widget
function tc_coach_comment_topiclist_widget_load() {
	register_widget( 'tc_coach_comment_topiclist_widget' );
}
add_action( 'widgets_init', 'tc_coach_comment_topiclist_widget_load' );
?>