<?php 
/*
Plugin Name: PostView
Plugin Author: QuiNguyen
Description: Plugin is used for counting post views and displaying topview
Version: 1.0
Author: URI: http://quicoder.16mb.com
*/
function postview_set($post_id){
	$count_key='postview_number';
	$count = get_post_meta($post_id,$count_key,true);  // $count is string variable
	if ($count==' '){
		$count=0;  // $count is integer variable
		delete_post_meta($post_id,$count_key);
		add_post_meta($post_id,$count_key,'0');
		}
		else {
			$count++;
			update_post_meta($post_id,$count_key,$count);
		}
	}
function postview_get($post_id){
	$count_key='postview_number';
	$count = get_post_meta($post_id,$count_key,true);  // $count is string variable
	if ($count==''){
		delete_post_meta($post_id,$count_key);
		add_post_meta($post_id,$count_key,'0');
		return '0 view';
		}
		return $count. ' views';
	}
remove_action( 'wp_head', 'adjacent_posts_rel_link_wp_head', 10, 0);
/* 
 * Top View Widget
 */

 //  Step 1: Init Widget
function create_topview_widget(){
	register_widget('Topview_Widget');
}
add_action('widgets_init','create_topview_widget');

//  Step 2: Create class and methods
/*
 *  Create Topview_Widget class
 */
class Topview_Widget extends WP_Widget{

// Step 3: Widget setting: name, base ID
    function __construct(){
    	parent::__construct(
    		'topview',
    		'Top View Posts',    		array(
    			'classname' => 'topview',
    			'description'=>'Widget shows top view articles')
    		);
    } 

//Step 4:  Create form for widget
        function form($instance) {
        $default = array(
            'title' => 'Top View Posts',
            'postnum' => 5,
            'postdate' => 30
        );
        $instance = wp_parse_args( (array) $instance, $default );
        $title = esc_attr( $instance['title'] );
        $postnum = esc_attr( $instance['postnum'] );
        $postdate = esc_attr( $instance['postdate'] );
 
        echo "<label>Tiêu đề:</label> <input class='widefat' type='text' name='".$this->get_field_name('title')."' value='".$title."' />";
        echo "<label>Số lượng bài viết:</label> <input class='widefat' type='number' name='".$this->get_field_name('postnum')."' value='".$postnum."' />";
        echo "<label>Độ tuổi của bài viết (ngày)</label> <input class='widefat' type='number' name='".$this->get_field_name('postdate')."' value='".$postdate."' />";
    }

// Step 5: save widget form
    function update($new_instance, $old_instance) {
        $instance = $old_instance;
        $instance['title'] = strip_tags($new_instance['title']);
        $instance['postnum'] = strip_tags($new_instance['postnum']);
        $instance['postdate'] = strip_tags($new_instance['postdate']);
        return $instance;
    }

// Step6: show widget
    function widget($args, $instance){
    	global $postdate; // used for filter_where
    	extract( $args );
    	// apply_filters( 'widget_title', empty( $instance['title'] ) ? __( 'Pages' ) : $title, $instance, $this->id_base ); 
        $title = apply_filters( 'widget_title', $instance['title'] );
    	$postnum = $instance['postnum'];
    	$postdate = $instance['postdate'];
    	 
    	echo $before_widget;
    	echo $before_title.$title.$after_title;

    	$topview_query_args = array(
    		'posts_per_page' => $postnum,
    		'meta_key' => 'postview_number',
    		'orderby' => 'meta_value_num',
    		'order' => 'DESC',
    		'ignore_sticky_posts' => -1

    		);

    	// Get post based on posted date
    	function filter_where( $where = ' '){
    		global $postdate;
    		$where .= "AND post_date > ' ".date('Y-m-d', strtotime('-'.$postdate.' days'))." ' ";
    		return $where;
    	}
    	add_filter('posts_where','filter_where');

    	$topview_query = new WP_Query($topview_query_args);

    	remove_filter('posts_where','filter_where'); // Remove to avoid affecting other queries

    	if($topview_query->have_posts()):
    		echo "<ul>";
    	     while  ($topview_query->have_posts()):
    	     	$topview_query->the_post(); ?>
    	     <li> <?php if (has_post_thumbnail()) 
    	     				the_post_thumbnail('thumbnail');
    	     				else
    	     					echo "</br><img src='http://dummyimage.com/50/000/fff&text=Qui Nguyen'>";
    	     ?><a href="<?php the_permalink(); ?>"  title="<?php the_title(); ?>"> <?php the_title(); ?></a> </li>

    	     <?php endwhile;
    	     echo "</ul>";
    	     endif;
    	     echo $after_widget;
    }
}

/*
 * Insert Plugins-CSS to theme
 */
function custom_styles() {
 
        wp_register_style( 'topview-css', plugins_url( 'style.css', __FILE__ ) , false, false, 'all' );
        wp_enqueue_style( 'topview-css' );
 
}
add_action( 'wp_enqueue_scripts', 'custom_styles' );
 ?>