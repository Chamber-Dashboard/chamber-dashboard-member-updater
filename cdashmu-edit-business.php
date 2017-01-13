<?php

// ------------------------------------------------------------------------
// FORM TO EDIT BUSINESS LISTING
// ------------------------------------------------------------------------
 

function cdashmu_business_update_form(){ 
    wp_enqueue_style( 'cdashmu-member-updater', plugin_dir_url(__FILE__) . 'css/cdashmu-member-updater.css' );
	wp_enqueue_script( 'user-registration-form', plugin_dir_url(__FILE__) . 'js/member_updater.js', array( 'jquery' ) );
	wp_localize_script( 'user-registration-form', 'userregistrationformajax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' ) ) );
    
    $user_id = cdashmu_get_current_user_id();
    $member_options = get_option('cdashmu_options');
    if(!$user_id){
        echo "Please login <a href='" . $member_options['user_login_page'] . "'>here</a> to update your business";
        return;
    }
    $person_id = cdashmu_get_person_id_from_user_id($user_id, false);
    $business_id = cdashmu_get_business_id_from_person_id($person_id, false);  
    if(!$business_id){
        $person_id = cdashmu_get_person_id_from_user_id($user_id, true);
        $business_id = cdashmu_get_business_id_from_person_id($person_id, true);  
        if($business_id){
            echo "Your connection to the business has not been approved yet. Please contact your Chamber of Commerce.";
        }
        else{
            echo "You are not connected to a business. Please contact your Chamber of Commerce.";            
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
          <p>View your business here: <a href="<?php echo $business_url; ?> "><?php echo $business_url; ?></a></p>          
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
            $settings = array( 'editor_height' => '300', 'media_buttons' => false );
            
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
            $current_terms = wp_get_post_terms( $business_id, $taxonomy, $args ); 
            $terms = get_terms('business_category');
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
        $terms = wp_get_post_terms( $business_id, $level, $args );     
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
            $thumbnail = get_the_post_thumbnail( $business_id, 'thumbnail' ); 
        }
        else{
            $thumbnail_image = "There is no featured image set for your business.";
        }
    ?>
        <label for="featured_image"><?php echo __('Featured Image')?></label><br />
        <div id="featured_image">
            <?php echo $thumbnail_image; ?>
        </div><br />
        If you wish, you can upload a new featured image (<?php echo $member_options['bus_featured_image_width']; ?>px X <?php echo $member_options['bus_featured_image_height']; ?>px).  
        <input type="file" name="featured_image" id="featured_image_upload" value=""/>            
    </p>
    
            
        <?php
        // make location/address metabox data available
        global $buscontact_metabox;    
        $contactmeta = $buscontact_metabox->the_meta();
        $business_data['locations'] = $contactmeta['location'];  
        $i = 0;
        foreach( $contactmeta['location'] as $location_info ) {
         ?>
         <fieldset>
         <legend><?php echo __('Location and Address')?></legend>

        <p>
            <label for="bus_url"><?php echo __('Web Address')?></label>
            <input type="text" name="buscontact_meta[location][<?php echo $i; ?>][url]" value="<?php echo $location_info['url']; ?>"/>
        </p>
        
        <p>
            <label for="bus_loc_name"><?php echo __('Location Name')?></label>
            <input type="text" name="buscontact_meta[location][<?php echo $i; ?>][altname]" value="<?php echo $location_info['altname'];?>"/>
        </p>
        
        <p>
            <?php
                $display_location = $location_info['donotdisplay'];
            ?>
            <label for="display_location"><?php echo __('Do not display this location to the public?'); ?></label>
            <input type="checkbox" name="buscontact_meta[location][<?php echo $i; ?>][donotdisplay]" value="<?php echo $location_info['donotdisplay'];?>" <?php if ($display_location == '1') echo "checked='checked'"; ?> />
        </p>
        
        <p>
            <label for="bus_address"><?php echo __('Address')?></label>
            <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_address" name="buscontact_meta[location][<?php echo $i; ?>][address]" value="<?php echo $location_info['address']; ?>"/>
        </p>
        
        <p>
            <label for="bus_city"><?php echo __('City')?></label>
            <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_city" name="buscontact_meta[location][<?php echo $i; ?>][city]" value="<?php echo $location_info['city']; ?>"/>
        </p>
        
        <p>
            <label for="bus_state"><?php echo __('State')?></label>
            <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_state" name="buscontact_meta[location][<?php echo $i; ?>][state]" value="<?php echo $location_info['state']; ?>"/>
        </p>
        
        <p>
            <label for="bus_zip"><?php echo __('Zip')?></label>
            <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_zip" name="buscontact_meta[location][<?php echo $i; ?>][zip]" value="<?php echo $location_info['zip']; ?>"/>
        </p>
        
        <p>
            <label for="bus_hours"><?php echo __('Hours')?></label>
            <input type="text" name="buscontact_meta[location][<?php echo $i; ?>][hours]" value="<?php echo $location_info['hours']; ?>"/>
        </p>
        
        <div style="float:left; width:50%;">
        <h6>Phone Numbers</h6>
        <?php
            $loop_index_phone = 0;
            foreach($location_info['phone'] as $phone_info) {
        ?>
            <p>
                <label for="bus_phone_1">Phone Number</label>
                <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_phone_<?php echo $loop_index_phone; ?>_phonenumber" name="buscontact_meta[location][<?php echo $i; ?>][phone][<?php echo $loop_index_phone; ?>][phonenumber]" value="<?php echo $phone_info['phonenumber']; ?>" />
            </p>
            
            <p>
                <label for="bus_phone_1_type">Phone Number Type</label>
                <?php $selected = ' selected="selected"'; ?>
				<select name="buscontact_meta[location][<?php echo $i; ?>][phone][<?php echo $loop_index_phone; ?>][phonetype]">
					<option value=""></option>
					<?php $options = get_option('cdash_directory_options');
				 	$phonetypes = $options['bus_phone_type'];
				 	$typesarray = explode( ",", $phonetypes);
				 	foreach ($typesarray as $type) { ?>
				 		<option value="<?php echo $type; ?>" <?php if ($phone_info['phonetype'] == $type) echo $selected; ?>><?php echo $type; ?></option>
				 	<?php } ?>
				</select>
            </p>
        <?php
                $loop_index_phone++;
            }
            
        ?>
        </div>
        <div style="float:left; width:50%;">

        <h6>Email Addresses</h6>
        <?php
            $loop_index_email = 0;
            foreach($location_info['email'] as $email_info) {
        ?>
        <p>
            <label for="bus_email_1">Email Address</label>
            <?php $selected = ' selected="selected"'; ?>            
            <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_email_<?php echo $loop_index_email; ?>_emailaddress" name="buscontact_meta[location][<?php echo $i; ?>][email][<?php echo $loop_index_email; ?>][emailaddress]" value="<?php echo $email_info['emailaddress']; ?>"/>
        </p>
                
        <p>
            <label for="bus_email_1_type">Email Address Type</label>
				<select name="buscontact_meta[location][<?php echo $i; ?>][email][<?php echo $loop_index_email; ?>][emailtype]">
					<option value=""></option>
					<?php $options = get_option('cdash_directory_options');
				 	$emailtypes = $options['bus_email_type'];
				 	$typesarray = explode( ",", $emailtypes);
				 	foreach ($typesarray as $type) { ?>
				 		<option value="<?php echo $type; ?>" <?php if ($email_info['emailtype'] == $type) echo $selected; ?>><?php echo $type; ?></option>
				 	<?php } ?>
				</select>

        </p>
        <?php
                $loop_index_email++;
            }
        ?>    
        
        </div>        
             <button type="button" id="copy_billing_address_<?php echo $i; ?>" name="copy_billing_address_<?php echo $i; ?>" class="copy_billing_address"><?php echo __('Set as Billing Address'); ?></button><span id="billing_copy_message_<?php echo $i; ?>" class="message"></span>
        <br /><br />
    </fieldset>       
    <?php
            $i++;
    	 }
    ?>
         
    
    <fieldset>
        <legend>Social Media Links</legend>
        <div id="social_media" class="social_media_div">            
            <?php
                //$social_media_list = "";
                $social_media_list = array(
                        "avvo"  => "Avvo",
                        "facebook"  =>  "Facebook",
                        "flickr"    =>  "Flickr",
                        "google"  =>  "Google +",
                        "instagram"  =>  "Instagram",
                        "linkedin"  =>  "LinkedIn",
                        "pinterest"  =>  "Pinterest",
                        "tripadvisor"  =>  "Trip Advisor",
                        "tumblr"  =>  "Tumblr",
                        "twitter"  =>  "Twitter",
                        "urbanspoon"  =>  "Urbanspoon",
                        "vimeo" =>  "Vimeo",
                        "website"   =>  "Website",
                        "youtube"   =>  "YouTube",
                        "yelp"  =>  "Yelp"
                    );
            ?>
            <div id="buscontact_meta_social_template_socialservice" class="social_media_child" style="display:none;">
                <label for="buscontact_meta_social_template_socialservice">Social Media Service</label>     
                <select name="buscontact_meta_social_template_socialservice">
                    <option value=""></option>
                    <?php
                    foreach($social_media_list as $key=>$value){
                    ?>   
                    <option value="<?php echo $key; ?>" <?php if($social_info['socialservice'] == $key){ echo $selected;}?>><?php echo $value; ?></option>
                    <?php
                    }
                    ?>				
                </select>

                <label for="buscontact_meta_social_template_socialurl">Social Media Url</label>
                <input type="text" name="buscontact_meta_social_template_socialurl" value="<?php echo $social_info['socialurl']; ?>" /><br />
                <span class="remove">
                    <input type="checkbox" name="social_media_remove_template" class="social_media_remove" id="social_media_remove_template" value="" />Delete
                </span> <br /><br />      
            </div>
            <?php
                //$business_data['social'] = $contactmeta['social']; 
                $i = 0;
                foreach( $contactmeta['social'] as $social_info ) {
                $selected = ' selected="selected"';                                                   
            ?>            
            <div id="buscontact_meta_social_<?php echo $i; ?>_socialservice" class="social_media_child">               
                <label for="buscontact_meta[social][<?php echo $i; ?>][socialservice]">Social Media Service</label>     
                <select name="buscontact_meta[social][<?php echo $i; ?>][socialservice]">
                    <option value=""></option>
                    <?php
                    foreach($social_media_list as $key=>$value){
                    ?>   
                    <option value="<?php echo $key; ?>" <?php if($social_info['socialservice'] == $key){ echo $selected;}?>><?php echo $value; ?></option>
                    <?php
                    }
                    ?>				
                </select>

                <label for="buscontact_meta[social][<?php echo $i; ?>][socialurl]">Social Media Url</label>
                <input type="text" name="buscontact_meta[social][<?php echo $i; ?>][socialurl]" value="<?php echo $social_info['socialurl']; ?>" /><br />
                <span class="remove">
                    <input type="checkbox" name="social_media_remove_<?php echo $i; ?>" class="social_media_remove" id="social_media_remove_<?php echo $i; ?>" value="" />Delete
                </span> <br /><br />      
            </div>
            <?php
                    $i++;
                }
            if(empty($social_media_list)){
                echo "There are no social media links selected. Click here to add social media links to your business.";
            }

            ?>
        </div><!--end of social media div-->
        <button type="button" id="add_social_media" class="button">Add Social Media Links</button>
    </fieldset>
    
    <fieldset>
        <legend>Billing Address</legend>
        <?php
            global $billing_metabox;    
            $billingmeta = $billing_metabox->the_meta();            
        ?>
        <p>
            <label for="billing_address">Address</label>
            <input type="text" id="billing_address" name="billing_address" value="<?php echo $billingmeta['billing_address']; ?>"/>
        </p>
        
        <p>
            <label for="billing_city">City</label>
            <input type="text" id="billing_city" name="billing_city" value="<?php echo $billingmeta['billing_city']; ?>"/>
        </p>
        
        <p>
            <label for="billing_state">State</label>
            <input type="text" id="billing_state" name="billing_state" value="<?php echo $billingmeta['billing_state']; ?>"/>
        </p>
        
        <p>
            <label for="billing_zip">Zip</label>
            <input type="text" id="billing_zip" name="billing_zip" value="<?php echo $billingmeta['billing_zip']; ?>"/>
        </p>
        
        <p>
            <label for="billing_email">Billing Email</label>
            <input type="text" id="billing_email" name="billing_email" value="<?php echo $billingmeta['billing_email']; ?>"/>
        </p>
        
        <p>
            <label for="billing_phone">Billing Phone</label>
            <input type="text" id="billing_phone" name="billing_phone" value="<?php echo $billingmeta['billing_phone']; ?>"/>
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
     foreach($social as $social_element){
         $social_array[] = $social_element;
     }
     update_post_meta($business_id, '_cdash_social', $social_array);

     
     //UPDATING THE BILLING ADDRESS FIELDS
     
     //Gather the variables from the form
     $billing_address = $_POST['billing_address'];
     $billing_city = $_POST['billing_city'];
     $billing_state = $_POST['billing_state'];
     $billing_zip = $_POST['billing_zip'];
     $billing_email = $_POST['billing_email'];
     $billing_phone = $_POST['billing_phone'];
     
     //Billing Fields
     // add a serialised array for wpalchemy to work - see http://www.2scopedesign.co.uk/wpalchemy-and-front-end-posts/
		$fields = array( 
			'_cdash_billing_address', 
			'_cdash_billing_city', 
			'_cdash_billing_state', 
			'_cdash_billing_zip', 
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
        update_post_meta( $business_id, '_cdash_billing_email', $billing_email );
		update_post_meta( $business_id, '_cdash_billing_phone', $billing_phone );

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