<?php

// ------------------------------------------------------------------------
// FORM TO EDIT BUSINESS LISTING
// ------------------------------------------------------------------------
 

function cdashmu_business_update_form(){ 
    $user_id = cdashmu_get_current_user_id();
    if(!$user_id){
        return "Please login <a href='" . $member_options['user_login_page'] . "'>here</a> to update your business";
    }
    $person_id = cdashmu_get_person_id_from_user_id($user_id, false);
    $business_id = cdashmu_get_business_id_from_person_id($person_id, false);  
    if(!$business_id){
        return "You are not connected to a business. Please contact your Chamber of Commerce.";
    }
    
    // Enqueue stylesheet                
    wp_enqueue_style( 'cdashmu-member-updater', plugin_dir_url(__FILE__) . 'css/cdashmu-member-updater.css' );
    ?>        
    <form action="<?php echo $_SERVER['REQUEST_URI']; ?>" method="post" id="cdashmu_business_update_form">
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
        <label for="bus_name"><?php echo __('Business Name')?> *</label>
        <input type="text" name="bname" id="bname" required value="<?php echo $bus_title; ?>">
    </p>
    
    <p>
        <label for="bus_desc">Business Description</label>
        <input type="textarea" name="bdesc" id="bdesc" value="<?php echo $bus_content; ?>"/>
    </p>
    
    <!--Display Current Categoty-->
    <?php 
    $taxonomy = 'business_category';
    $terms = wp_get_post_terms( $business_id, $taxonomy, $args ); 
    ?>
    <p>
        <label for="bus_cat">Current Business Category</label>
        <?php 
          foreach($terms as $single_biz_category) {
                echo $single_biz_category->name; //do something here
                echo "<br />";
          }
        ?>
    </p>
    
    <p>
        <label for="bus_cat">Add New Business Category</label>
        <?php

        $category = 'business_category';
        //$terms = get_terms($taxonomy); // Get all terms of a taxonomy
        $terms = wp_get_post_terms( $business_id, $category, $args ); 
        if ( $terms && !is_wp_error( $terms ) ) :
        ?>        
            <select name="bus_cat">
                <?php foreach ( $terms as $term ) { ?>
                    <option name = "<?php echo get_term_link($term->slug, $category); ?>"><?php echo $term->name; ?></option>
                <?php } ?>
            </select>
        <?php endif;?>
    </p>
    
    <p>
        <label for="membership_level">Membership Level</label>
        <?php

        $level = 'membership_level';
        //$terms = get_terms($taxonomy); // Get all terms of a taxonomy
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
    	if( $single_link == "yes" ) {
    		$logo = "<a href='" . get_the_permalink() . "'>" . wp_get_attachment_image($logometa['buslogo'], 'thumb', 0, $logoattr ) . "</a>";
		} else {
		$logo= wp_get_attachment_image($logometa['buslogo'], 'thumb', 0, $logoattr );
    	}
	}
    ?>
        <label for="bus_logo">Logo</label> <br /><?php echo $logo; ?> <br />(If you wish, you can upload a new logo (image dimensions).
        <input type="file" name="logo" value=""/>
    </p>
    
    <p>
    <?php
        // check if the post has a Post Thumbnail assigned to it.
        if ( has_post_thumbnail() ) {
            $thumbnail = get_the_post_thumbnail( $business_id, 'thumbnail' ); 
        	$thumbnail_image = "<a href='" . get_the_permalink() . "'>" . $thumbnail . "</a>";
        }
        else{
            $thumbnail_image = "There is no featured image set for your business.";
        }
    ?>
        <label for="featured_image">Featured Image</label><br />
        <?php echo $thumbnail_image; ?><br />
        (If you wish, you can upload a new featured image (image dimensions).
        <input type="file" name="logo" value=""/>
    </p>
    
            
        <?php
        // make location/address metabox data available
        global $buscontact_metabox;    
        $contactmeta = $buscontact_metabox->the_meta();
        $business_data['locations'] = $contactmeta['location'];  
        foreach( $contactmeta['location'] as $location_info ) {
         ?>
         <fieldset>
         <legend>Location and Address</legend>

        <p>
            <label for="bus_url">Web Address</label>
            <input type="text" name="<?php echo $location_info['url']; ?>" value="<?php echo $location_info['url']; ?>"/>
        </p>
        
        <p>
            <label for="bus_loc_name">Location Name</label>
            <input type="text" name="<?php echo $location_info['altname'];?>" value="<?php echo $location_info['altname'];?>"/>
        </p>
        
        <p>
            <label for="display_location">Do you want to display this location to the public?</label>
            <input type="checkbox" name="<?php echo $location_info['donotdisplay']; ?>" value="<?php echo $location_info['donotdisplay'];?>" />
        </p>
        
        <p>
            <label for="bus_address">Address</label>
            <input type="textarea" name="<?php echo $location_info['address']; ?>" value="<?php echo $location_info['address']; ?>"/>
        </p>
        
        <p>
            <label for="bus_city">City</label>
            <input type="text" name="<?php echo $location_info['city']; ?>" value="<?php echo $location_info['city']; ?>"/>
        </p>
        
        <p>
            <label for="bus_state">State</label>
            <input type="text" name="<?php echo $location_info['state']; ?>" value="<?php echo $location_info['state']; ?>"/>
        </p>
        
        <p>
            <label for="bus_zip">Zip</label>
            <input type="text" name="<?php echo $location_info['zip']; ?>" value="<?php echo $location_info['zip']; ?>"/>
        </p>
        
        <label>Use this for the billing address.</label>
        <input type="checkbox" name="same_billing_address" />
        <br />
        
        <div style="float:left; width:50%;">
        <h6>Phone Numbers</h6>
        <?php
        //if( is_array( $location_info['phone'] ) ) {
            //$loop_index_phone = 0;
            foreach($location_info['phone'] as $phone_info) {
        ?>
            <p>
                <label for="bus_phone_1">Phone Number</label>
                <input type="text" name="buscontact_meta[location][phone][phonenumber]" value="<?php echo $phone_info['phonenumber']; ?>" />
            </p>
            
            <p>
                <label for="bus_phone_1_type">Phone Number Type</label>
                <input type="text" name="buscontact_meta[location][phone][phonetype]" value="<?php echo $phone_info['phonetype']; ?>"/>
            </p>
        <?php
            }
            //$loop_index_phone++;
        //}
        ?>
        
        <p><a href="">Add another phone number</a></p>
        <p>
            <label for="remove_all_phone_numbers">Remove All Phone Numbers</label>
            <input type="checkbox" name="remove_all_phone_numbers" value=""/>
        </p>
        </div>
        <div style="float:left; width:50%;">

        <h6>Email Addresses</h6>
        <?php
            foreach($location_info['email'] as $email_info) {
        ?>
        <p>
            <label for="bus_email_1">Email Address</label>
            <input type="text" name="bus_email_1" value="<?php echo $email_info['emailaddress']; ?>"/>
        </p>
        
        <p>
            <label for="bus_email_1_type">Email Address Type</label>
            <input type="text" name="bus_email_1_type" value="<?php echo $email_info['emailtype']; ?>"/>
        </p>
        <?php
            }
        ?>
        
        <p><a href="">Add another Email Address</a></p>
        
        <p>
            <label for="remove_all_email_addresses">Remove All Email Addresses</label>
            <input type="checkbox" name="remove_all_email_addresses" value=""/>
        </p>
        </div>        
    </fieldset>       
    <?php
            //$loop_index++;
    	 }
    ?>
         
    
    <fieldset>
        <legend>Social Media Links</legend>
        <p>
                <?php
                    //$business_data['social'] = $contactmeta['social'];  
                    foreach( $contactmeta['social'] as $social_info ) {
                ?>
                        <label name="<?php echo $social_info['socialservice']; ?>"><?php echo $social_info['socialservice']; ?></label>
                        <input type="text" name="<?php echo $social_info['socialurl']; ?>" value="<?php echo $social_info['socialurl']; ?>" /><br />
                        <label class="remove" name = "">Remove this Social Media Link</label>
                        <input class="remove" type="checkbox" name="" /><br />
                       
                <?php
                    }
                ?>
        </p>
        <a href="">Add another Social Media Link</a>
    </fieldset>
    
    <fieldset>
        <legend>Billing Address</legend>
        <?php
            global $billing_metabox;    
            $billingmeta = $billing_metabox->the_meta();            
        ?>
        <p>
            <label for="billing_address">Address</label>
            <input type="textarea" name="billing_address" value="<?php echo $billingmeta['billing_address']; ?>"/>
        </p>
        
        <p>
            <label for="billing_city">City</label>
            <input type="text" name="billing_city" value="<?php echo $billingmeta['billing_city']; ?>"/>
        </p>
        
        <p>
            <label for="billing_state">State</label>
            <input type="text" name="billing_state" value="<?php echo $billingmeta['billing_state']; ?>"/>
        </p>
        
        <p>
            <label for="billing_zip">Zip</label>
            <input type="text" name="billing_zip" value="<?php echo $billingmeta['billing_zip']; ?>"/>
        </p>
        
        <p>
            <label for="billing_country">Country</label>
            <input type="text" name="billing_country" value="<?php echo $billingmeta['billing_country']; ?>"/>
        </p>
        
        <p>
            <label for="billing_email">Billing Email</label>
            <input type="text" name="billing_email" value="<?php echo $billingmeta['billing_email']; ?>"/>
        </p>
        
        <p>
            <label for="billing_phone">Billing Phone</label>
            <input type="text" name="billing_phone" value="<?php echo $billingmeta['billing_phone']; ?>"/>
        </p>
    
    </fieldset>	
    
    <fieldset>
        <legend>Connected People</legend>
            <p>
                <label for="connected_people">Connected People</label>
                Display connected people here.
            </p>
            
            Add more connections
        
    </fieldset>
        
    
    <input type="submit" name="submit" value="Submit" />	
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
        'post_title' => $_POST['bname'],
        'post_content' => $_POST['bdesc']        
     );
     
     wp_update_post( $update_business_fields );
     
     /*global $buscontact_metabox;
     $contactmeta = $buscontact_metabox->the_meta();
     $contactmeta = $_POST['buscontact_meta'];
     update_post_meta($business_id, '_cdash_location', $contactmeta['location']);*/
    
 }
       
?>