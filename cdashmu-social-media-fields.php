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
                <option value="<?php echo $key; ?>" <?php if( isset($social_info['socialservice']) && $social_info['socialservice'] == $key){ echo $selected;}?>><?php echo $value; ?></option>
                <?php
                }
                ?>
            </select>

            <label for="buscontact_meta_social_template_socialurl">Social Media Url</label>
            <input type="text" name="buscontact_meta_social_template_socialurl" value="<?php if(isset($social_info['socialurl'])) echo $social_info['socialurl']; ?>" /><br />
            <span class="remove">
                <input type="checkbox" name="social_media_remove_template" class="social_media_remove" id="social_media_remove_template" value="" />Delete
            </span> <br /><br />
        </div>
        <?php
    $i = 0;
            if(isset($contactmeta['social'])){
      //echo "Social media is set.";
      $business_data['social'] = $contactmeta['social'];

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
    }else{
      //echo "Social media is not set.";
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
    }
        if(empty($social_media_list)){
            echo "There are no social media links selected. Click here to add social media links to your business.";
        }

        ?>
    </div><!--end of social media div-->
    <button type="button" id="add_social_media" class="button">Add Social Media Links</button>
</fieldset>
