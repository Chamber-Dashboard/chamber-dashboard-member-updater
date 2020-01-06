<?php

// ------------------------------------------------------------------------
// FORM TO EDIT BUSINESS LISTING
// ------------------------------------------------------------------------


function cdashmu_business_update_form(){
    wp_enqueue_style( 'cdashmu-member-updater', plugin_dir_url(__FILE__) . 'css/cdashmu-member-updater.css' );
	wp_enqueue_script( 'user-registration-form', plugin_dir_url(__FILE__) . 'js/member_updater.js', array( 'jquery' ) );
	wp_localize_script( 'user-registration-form', 'userregistrationformajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );

    $user_id = cdashmm_get_current_user_id();
    $member_options = get_option('cdashmu_options');
    $mm_options = get_option('cdashmm_options');
    $login_page = '';
    if(isset($mm_options['cdashmm_member_login_form']) && $mm_options['cdashmm_member_login_form'] != ''){
      $login_page = cdashmm_get_login_page_url();
    }elseif(isset($member_options['user_login_page'])){
      $login_page = $member_options['user_login_page'];
    }
    $business_edit_page_slug = get_queried_object()->post_name;

    if(!$user_id){
        echo __("<p>Please login <a href='" . $login_page . "'>here</a> to update your business.</p>", "cdashmu");
        return;
    }
    if(!cdashmu_is_business_editor($user_id)){
      echo __("<p>You are not authorized to edit your business. Please contact your site admin for more details.</p>", "cdashmu");
      return;
    }
    $person_id = cdashmu_get_person_id_from_user_id($user_id, false);
    $business_id = cdashmu_get_business_id_from_person_id($person_id, false);
    if(!$business_id){
        $person_id = cdashmu_get_person_id_from_user_id($user_id, true);
        $business_id = cdashmu_get_business_id_from_person_id($person_id, true);
        if($business_id){
            echo __("Your connection to the business has not been approved yet. Please contact your Chamber of Commerce.", "cdashmu");
        }
        else{
            echo __("You are not connected to a business. Please contact your Chamber of Commerce.", "cdashmu");
        }
        return;
    }

    // Enqueue stylesheet
    wp_enqueue_style( 'cdashmu-member-updater', plugin_dir_url(__FILE__) . 'css/cdashmu-member-updater.css' );
    ?>
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="cdashmu_business_update_form" class="cdash_form" enctype="multipart/form-data">
    <?php
    $query = new WP_Query( array( 'post_type' => 'business', 'p' =>  $business_id) );

    if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post();

        $business_url = cdashmu_get_business_url_from_business_id($business_id);
        $bus_title = get_the_title();
        $bus_content = get_the_content();

        global $buscontact_metabox;
        $contactmeta = $buscontact_metabox->the_meta();

        if(isset($_POST['submit'])){
            cdashmu_update_business_data($business_id);
        ?>
          <p>Your business has been successfully updated.<br />
          <p><?php  echo __('View your business here:', 'cdash-mu'); ?> <a href="<?php echo $business_url; ?> "><?php echo $business_url; ?></a></p>
          <p><a href='<?php echo $login_page;?>'><?php echo __('View your Account', 'cdash-mu'); ?></a></p>
        <?php
            return;
        }
        ?>

    <p>
        <label for="bus_name"><?php echo __('Business Name')?></label>
        <?php echo $bus_title; ?>
    </p>

    <p>
        <label for="bus_desc"><?php echo __('Business Description')?></label>
        <?php

            $content = $bus_content;
            $editor_id = 'bdesc';
            $settings = array( "wpautop" => true, 'editor_height' => '300', 'media_buttons' => false );
            wp_editor( $content, $editor_id, $settings );
        ?>
    </p>

    <!--Display Current Categoty-->
    <?php
        $taxonomy = 'business_category';
        $current_terms = wp_get_post_terms( $business_id, $taxonomy);
    ?>
    <p>
        <label for="bus_cat"><?php echo __('Business Categories')?></label>
        <p> <?php echo __('Current categories have been checked. If you would like to add more categories, please select them from the list below. If you would like to remove the current categories, please un-check them.')?></p>
        <?php
        $taxonomy = 'business_category';
        $current_terms = wp_get_post_terms( $business_id, $taxonomy );
		    $terms = get_terms( 'business_category', array(
			       'hide_empty' => false,
			  ) );
        foreach($terms as $single_category){
            $category_slug = $single_category->slug;
        ?>
            <input type="checkbox" name="business_category[]" value="<?php echo $single_category->slug; ?>"
            <?php
            foreach($current_terms as $single_biz_category) {
              $current_category_list = $single_biz_category->slug; //do something here
              if($current_category_list == $single_category->slug){
                echo "checked";
              }
            }
            ?>
            />
        <?php
        echo $single_category-> name;
        echo "<br />";
        }
        ?>
    </p>

    <p>
        <label for="membership_level"><?php echo __('Membership Level')?></label>
        <?php
        $level = 'membership_level';
        $terms = wp_get_post_terms( $business_id, $level );
        if ( $terms && !is_wp_error( $terms ) ) :
            foreach ( $terms as $term ) {
              echo $term->name;
            }
        endif;
        ?>
    </p>

    <p>
    <?php
    // make logo metabox data available
	  global $buslogo_metabox;
	  $logometa = $buslogo_metabox->the_meta();
    if( isset( $logometa['buslogo'] ) ) {
        $logoattr = array(
            'class'	=> 'logo',
        );
		    $logo= wp_get_attachment_image($logometa['buslogo'], 'thumb', 0, $logoattr );
	   }
    else{
        $logo = "There is no logo set for your business.";
    }
    ?><br />
       <?php $member_options = get_option('cdashmu_options');  ?>
        <label for="bus_logo"><?php echo __('Logo')?></label><?php echo $logo; ?> <br />If you wish, you can upload a new logo (<?php echo $member_options['bus_logo_image_width']; ?>px X <?php echo $member_options['bus_logo_image_height']; ?>px ).
        <input type="file" name="bus_logo" value=""/>
    </p>

    <p>
    <?php
        // check if the post has a Post Thumbnail assigned to it.
        if ( has_post_thumbnail() ) {
            $thumbnail_image = get_the_post_thumbnail( $business_id, 'thumbnail' );
        }
        else{
            $thumbnail_image = "There is no featured image set for your business.";
        }
    ?>
        <label for="featured_image"><?php echo __('Featured Image')?></label>
            <?php echo $thumbnail_image; ?>
        <br />
        If you wish, you can upload a new featured image (<?php echo $member_options['bus_featured_image_width']; ?>px X <?php echo $member_options['bus_featured_image_height']; ?>px).
        <input type="file" name="featured_image" id="featured_image_upload" value=""/>
    </p>
    <?php
        // make location/address metabox data available
        global $buscontact_metabox;
        $contactmeta = $buscontact_metabox->the_meta();
        //$business_data['locations'] = $contactmeta['location'];
        $i = 0;
        ?>
        <fieldset>
        <legend><?php echo __('Location and Address')?></legend>
        <?php
        if(!isset($contactmeta['location'])){
          //echo "show the empty location fields";
          include 'cdashmu-location-fields.php';
        }else{
          //echo "Show the fields with values";
        foreach( $contactmeta['location'] as $location_info ) {
          include 'cdashmu-location-fields.php';
         ?>
        <!--<p>
            <label for="bus_url"><?php echo __('Web Address')?></label>
            <input type="text" name="buscontact_meta[location][<?php echo $i; ?>][url]" value="<?php if(isset($location_info['url'])) echo $location_info['url']; ?>"/>
        </p>-->
    </fieldset>
    <?php
            $i++;
    	 }
     }
    ?>
    <?php
      include 'cdashmu-social-media-fields.php';
    ?>

    <fieldset>
        <legend>Billing Address</legend>
        <?php
            global $billing_metabox;
            $billingmeta = $billing_metabox->the_meta();
        ?>
        <p>
            <label for="billing_address">Address</label>
            <input type="text" id="billing_address" name="billing_address" value="<?php if(isset($billingmeta['billing_address'])) echo $billingmeta['billing_address']; ?>"/>
        </p>

        <p>
            <label for="billing_city">City</label>
            <input type="text" id="billing_city" name="billing_city" value="<?php if(isset($billingmeta['billing_city'])) echo $billingmeta['billing_city']; ?>"/>
        </p>

        <p>
            <label for="billing_state">State</label>
            <input type="text" id="billing_state" name="billing_state" value="<?php if(isset($billingmeta['billing_state'])) echo $billingmeta['billing_state']; ?>"/>
        </p>

        <p>
            <label for="billing_zip">Zip</label>
            <input type="text" id="billing_zip" name="billing_zip" value="<?php if(isset($billingmeta['billing_zip'])) echo $billingmeta['billing_zip']; ?>"/>
        </p>

        <p>
            <label for="billing_zip">Country</label>
            <input type="text" id="billing_country" name="billing_country" value="<?php if(isset($billingmeta['billing_country'])) echo $billingmeta['billing_country']; ?>"/>
        </p>

        <p>
            <label for="billing_email">Billing Email</label>
            <input type="text" id="billing_email" name="billing_email" value="<?php if(isset($billingmeta['billing_email'])) echo $billingmeta['billing_email']; ?>"/>
        </p>

        <p>
            <label for="billing_phone">Billing Phone</label>
            <input type="text" id="billing_phone" name="billing_phone" value="<?php if(isset($billingmeta['billing_phone'])) echo $billingmeta['billing_phone']; ?>"/>
        </p>

    </fieldset>

    <input type="submit" name="submit" id="mu_edit_form" value="Submit" />
    </form>
    <?php

    endwhile; endif;
    wp_reset_query();

    wp_reset_postdata();

}

// Register a new shortcode: [cdashmu_update_business]
add_shortcode( 'cdashmu_update_business', 'cdashmu_business_update_form_shortcode' );

function cdashmu_business_update_form_shortcode(){
    ob_start();
    cdashmu_business_update_form();
    return ob_get_clean();
}

 function cdashmu_update_business_data($business_id){
     //$business_data['desc'] = $_POST['bdesc'];
     $update_business_fields = array(
        'ID' => $business_id,
        'post_content' => $_POST['bdesc']
     );
     wp_update_post( $update_business_fields );

     //Updating Business Categories
     $terms = [];
     if(!empty($_POST['business_category'])){
         $terms = $_POST['business_category'];
     }
     wp_set_object_terms( $business_id, $terms, 'business_category', false);

    if ($_FILES) {
        //UPDATING THE FEATURED IMAGE
        if($_FILES['featured_image']['name']){
            $attach_id = insert_attachment('featured_image',$business_id);
            set_post_thumbnail($business_id, $attach_id);
        }

        //UPDATING THE LOGO FIELDS
        if($_FILES['bus_logo']['name']){
            $attach_id = insert_attachment('bus_logo',$business_id);
            $fields = array('_cdash_buslogo');
            $str = $fields;
            update_post_meta($business_id, 'buslogo_meta_fields', $str );
            update_post_meta($business_id, '_cdash_buslogo', $attach_id);
        }
    }

     //UPDATING THE LOCATION FIELDS
     $fields = array('_cdash_location', '_cdash_social');
     $str = $fields;
     update_post_meta($business_id, 'buscontact_meta_fields', $str);

     // update locations
     $locations = $_POST['buscontact_meta']['location'];    //$locations[0] = first location, $locations[1] = second location
     update_post_meta($business_id, '_cdash_location', $locations);

     // update social
     $social_array = [];
     $social = $_POST['buscontact_meta']['social'];    //$social[0] = first social, $social[1] = second social
     if(!empty($social)){
         foreach($social as $social_element){
             $social_array[] = $social_element;
         }
         update_post_meta($business_id, '_cdash_social', $social_array);
     }

     //UPDATING THE BILLING ADDRESS FIELDS

     //Gather the variables from the form
     $billing_address = $_POST['billing_address'];
     $billing_city = $_POST['billing_city'];
     $billing_state = $_POST['billing_state'];
     $billing_zip = $_POST['billing_zip'];
     $billing_country = $_POST['billing_country'];
     $billing_email = $_POST['billing_email'];
     $billing_phone = $_POST['billing_phone'];

     //Billing Fields
     // add a serialised array for wpalchemy to work - see http://www.2scopedesign.co.uk/wpalchemy-and-front-end-posts/
		$fields = array(
			'_cdash_billing_address',
			'_cdash_billing_city',
			'_cdash_billing_state',
			'_cdash_billing_zip',
      '_cdash_billing_country',
      '_cdash_billing_email',
			'_cdash_billing_phone'
		);
		$str = $fields;
		update_post_meta( $business_id, 'billing_meta_fields', $str );

		// update each individual field
		update_post_meta( $business_id, '_cdash_billing_address', $billing_address );
		update_post_meta( $business_id, '_cdash_billing_city', $billing_city );
		update_post_meta( $business_id, '_cdash_billing_state', $billing_state );
		update_post_meta( $business_id, '_cdash_billing_zip', $billing_zip );
    update_post_meta( $business_id, '_cdash_billing_country', $billing_country );
    update_post_meta( $business_id, '_cdash_billing_email', $billing_email );
		update_post_meta( $business_id, '_cdash_billing_phone', $billing_phone );

     cdash_store_geolocation_data($business_id);

 }

function insert_attachment($file_handler,$post_id) {
    // check to make sure its a successful upload
    if ($_FILES[$file_handler]['error'] !== UPLOAD_ERR_OK) __return_false();

    require_once(ABSPATH . "wp-admin" . '/includes/image.php');
    require_once(ABSPATH . "wp-admin" . '/includes/file.php');
    require_once(ABSPATH . "wp-admin" . '/includes/media.php');

    $attach_id = media_handle_upload( $file_handler, $post_id );

    return $attach_id;
}

?>
