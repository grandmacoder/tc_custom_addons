<?php
// Creating the widget 
class tc_lern_coach_widget extends WP_Widget {

function __construct() {
parent::__construct(
// Base ID of your widget
'tc_lern_coach_widget', 

// Widget name will appear in UI
__('Coach corner', 'tc_lern_coach_widget'), 

// Widget description
array( 'description' => __( 'Set the coach corner by course id', 'tc_lern_coach_widget' ), ) 
);
}
// Creating widget front-end
// This is where the action happens
public function widget( $args, $instance ) {
global $wpdb;
$title = apply_filters( 'widget_title', $instance['title'] );
$course_id = apply_filters( 'widget_course_id', $instance['course_id'] );
$coach_page_id= apply_filters( 'widget_page_id', $instance['coach_page_id'] );
$current_user = wp_get_current_user();
//could be more than one coach
$courseCoachID=$wpdb->get_var($wpdb->prepare("Select coach_id  from wp_wpcw_course_extras where course_id= %d",$course_id)); 
$aCoaches=explode(",",$courseCoachID);
if (in_array($current_user->ID, $aCoaches)){
$outputLink=$args['before_title'] . $title . $args['after_title'];
$outputLink.= "<ul></i><a href=/?p=".$coach_page_id."&course_id=".$course_id.">See student progress</a><i class='fa fa-cog' style='font-size:20px; color: #3b8dbd;'></i></li>" ;
$outputLink .="<li><a id='share-website' href='#'>Share a website</a><i class='fa fa-globe' style='font-size:20px; color: #3b8dbd;'></i></li><li><a id='share-file'  href='#'>Share a file</a><i class='fa fa-file' style='font-size:20px; color: #3b8dbd;'></i></li></ul>";
}
// before and after widget arguments are defined by themes
echo $args['before_widget'];
// This is where you run the code and display the output
//get the coach id and current user id
echo __(  $outputLink , 'tc_lern_coach_widget' );
echo $args['after_widget'];
}
		
// Widget Backend 
public function form( $instance ) {
if ( isset( $instance[ 'title' ] ) ) {
$title = $instance[ 'title' ];
}
else {
$title = __( 'New title', 'tc_lern_coach_widget' );
}
if ( isset( $instance[ 'course_id' ] ) ) {
$course_id = $instance[ 'course_id' ];
}
else {
$course_id = __( 'Course ID', 'tc_lern_coach_widget' );
}
if ( isset( $instance[ 'coach_page_id' ] ) ) {
$coach_page_id = $instance[ 'coach_page_id' ];
}
else {
$coach_page_id = __( 'Coach page ID', 'tc_lern_coach_widget' );
}
// Widget admin form
?>
<p>
<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
<label for="<?php echo $this->get_field_id( 'course_id' ); ?>"><?php _e( 'Course ID:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'course_id' ); ?>" name="<?php echo $this->get_field_name( 'course_id'); ?>" type="text" value="<?php echo esc_attr( $course_id ); ?>" />
<label for="<?php echo $this->get_field_id( 'coach_page_id' ); ?>"><?php _e( 'Coach page ID:' ); ?></label> 
<input class="widefat" id="<?php echo $this->get_field_id( 'coach_page_id' ); ?>" name="<?php echo $this->get_field_name( 'coach_page_id'); ?>" type="text" value="<?php echo esc_attr( $coach_page_id ); ?>" />
</p>
<?php 
}
	
// Updating widget replacing old instances with new
public function update( $new_instance, $old_instance ) {
$instance = array();
$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
$instance['course_id'] = ( ! empty( $new_instance['course_id'] ) ) ? strip_tags( $new_instance['course_id'] ) : '';
$instance['coach_page_id'] = ( ! empty( $new_instance['coach_page_id'] ) ) ? strip_tags( $new_instance['coach_page_id'] ) : '';
return $instance;
}
} // Class tc_lern_coach_widget ends here

// Register and load the widget
function tc_lern_coach_widget_load() {
	register_widget( 'tc_lern_coach_widget' );
}
add_action( 'widgets_init', 'tc_lern_coach_widget_load' );
?>