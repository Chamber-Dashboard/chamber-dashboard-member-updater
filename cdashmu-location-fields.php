<p>
    <label for="bus_url"><?php echo __('Web Address')?></label>
    <input type="text" name="buscontact_meta[location][<?php echo $i; ?>][url]" value="<?php if(isset($location_info['url'])) echo $location_info['url']; ?>"/>
</p>

<p>
    <label for="bus_loc_name"><?php echo __('Location Name')?></label>
    <input type="text" name="buscontact_meta[location][<?php echo $i; ?>][altname]" value="<?php if(isset($location_info['altname'])) echo $location_info['altname']; ?>"/>
</p>

<p>
    <?php
    $display_location = '';
    if(isset($location_info['donotdisplay'])){
        $display_location = $location_info['donotdisplay'];
    }
    ?>
    <label for="display_location"><?php echo __('Do not display this location to the public?'); ?></label>
    <input type="checkbox" name="buscontact_meta[location][<?php echo $i; ?>][donotdisplay]" value="<?php echo $display_location; ?>" <?php if ($display_location == '1') echo "checked='checked'"; ?> />
</p>

<p>
    <label for="bus_address"><?php echo __('Address')?></label>
    <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_address" name="buscontact_meta[location][<?php echo $i; ?>][address]" value="<?php if(isset($location_info['address'])) echo $location_info['address']; ?>"/>
</p>

<p>
    <label for="bus_city"><?php echo __('City')?></label>
    <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_city" name="buscontact_meta[location][<?php echo $i; ?>][city]" value="<?php if(isset($location_info['city'])) echo $location_info['city']; ?>"/>
</p>

<p>
    <label for="bus_state"><?php echo __('State')?></label>
    <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_state" name="buscontact_meta[location][<?php echo $i; ?>][state]" value="<?php if(isset($location_info['state'])) echo $location_info['state']; ?>"/>
</p>

<p>
    <label for="bus_zip"><?php echo __('Zip')?></label>
    <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_zip" name="buscontact_meta[location][<?php echo $i; ?>][zip]" value="<?php if(isset($location_info['zip'])) echo $location_info['zip']; ?>"/>
</p>

<p>
    <label for="bus_country"><?php echo __('Country')?></label>
    <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_country" name="buscontact_meta[location][<?php echo $i; ?>][country]" value="<?php if(isset($location_info['country'])) echo $location_info['country']; ?>"/>
</p>

<!-- Set latitude and logintude to update the map when the address is updated-->

<p>
    <label for="bus_hours"><?php echo __('Hours')?></label>
    <input type="text" name="buscontact_meta[location][<?php echo $i; ?>][hours]" value="<?php if(isset($location_info['hours'])) echo $location_info['hours']; ?>"/>
</p>

<!--Phone Numbers-->
<div style="float:left; width:50%;">
<h6>Phone Numbers</h6>
<?php
    $loop_index_phone = 0;
if(isset($location_info['phone'])){
foreach($location_info['phone'] as $phone_info) {
?>
<p>
  <label for="bus_phone_1">Phone Number</label>
  <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_phone_<?php echo $loop_index_phone; ?>_phonenumber" name="buscontact_meta[location][<?php echo $i; ?>][phone][<?php echo $loop_index_phone; ?>][phonenumber]" value="<?php if(isset($phone_info['phonenumber'])) echo $phone_info['phonenumber']; ?>" />
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
}else{
?>
<p>
  <label for="bus_phone_1">Phone Number</label>
  <input type="text" id="buscontact_meta_location_<?php echo $i; ?>_phone_<?php echo $loop_index_phone; ?>_phonenumber" name="buscontact_meta[location][<?php echo $i; ?>][phone][<?php echo $loop_index_phone; ?>][phonenumber]" value="<?php if(isset($location_info['phonenumber'])) echo $location_info['phonenumber']; ?>" />
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
      <option value="<?php echo $type; ?>" <?php if ( isset($location_info['phonetype']) && $location_info['phonetype'] == $type) echo $selected; ?>><?php if(isset($type)) echo $type; //echo $type; ?>
      </option>
    <?php } ?>
  </select>
</p>
<?php
}

?>
</div>

<!--Email Addresses-->

        <div style="float:left; width:50%;">

        <h6>Email Addresses</h6>
        <?php
            $loop_index_email = 0;
			if(isset($location_info['email'])){
				foreach($location_info['email'] as $email_info) {
			?>
			<p>
				<label for="bus_email_1">Email Address</label>

				<input type="text" id="buscontact_meta_location_<?php echo $i; ?>_email_<?php echo $loop_index_email; ?>_emailaddress" name="buscontact_meta[location][<?php echo $i; ?>][email][<?php echo $loop_index_email; ?>][emailaddress]" value="<?php if(isset($email_info['emailaddress'])) echo $email_info['emailaddress']; ?>"/>
			</p>

			<p>
				<label for="bus_email_1_type">Email Address Type</label>
				<?php $selected = ' selected="selected"'; ?>
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
			}else{
			?>
				<p>
				<label for="bus_email_1">Email Address</label>
				<?php $selected = ' selected="selected"'; ?>
				<input type="text" id="buscontact_meta_location_<?php echo $i; ?>_email_<?php echo $loop_index_email; ?>_emailaddress" name="buscontact_meta[location][<?php echo $i; ?>][email][<?php echo $loop_index_email; ?>][emailaddress]" value="<?php if(isset($location_info['emailaddress'])) echo $location_info['emailaddress']; ?>"/>
			</p>

			<p>
				<label for="bus_email_1_type">Email Address Type</label>
					<select name="buscontact_meta[location][<?php echo $i; ?>][email][<?php echo $loop_index_email; ?>][emailtype]">
						<option value=""></option>
						<?php $options = get_option('cdash_directory_options');
						$emailtypes = $options['bus_email_type'];
						$typesarray = explode( ",", $emailtypes);
						foreach ($typesarray as $type) { ?>
							<option value="<?php echo $type; ?>" <?php if( isset($location_info['emailtype']) && $location_info['emailtype'] == $type) echo $selected; ?>><?php if(isset($type)) echo $type; ?></option>
						<?php } ?>
					</select>

			</p>
			<?php
			}
        ?>

        </div>

        <button type="button" id="copy_billing_address_<?php echo $i; ?>" name="copy_billing_address_<?php echo $i; ?>" class="copy_billing_address"><?php echo __('Set as Billing Address'); ?></button><span id="billing_copy_message_<?php echo $i; ?>" class="message"></span>
        <br /><br />
