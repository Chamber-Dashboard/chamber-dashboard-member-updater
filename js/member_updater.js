jQuery(document).ready(function ($) {

// when business name changes, check to see whether the business is already in the database
    $('#bus_name').change(function (evt) {
        var url = userregistrationformajax.ajaxurl;
        var name = $('#bus_name').val();
        var nonce = $('#cdashmu_user_registration_nonce').val();
        var data = {
            'action': 'cdashmu_find_existing_business',
            'nonce': nonce,
            'bus_name': name
        };
        // insert the business selection form into the page
        $.post(url, data, function (response) {
            jQuery("#business-picker").html(response);
        });

    });
	
	
	// when a business is selected, fill in the form
    $('#business-picker').on('change', 'input[name=business_id]:radio', function (evt) {
        var url = userregistrationformajax.ajaxurl;
        var business_id = $('input[name=business_id]:checked', '#cdashmu_user_registration_form').val()
        var nonce = $('#cdashmu_user_registration_nonce').val();
        var data = {
            'action': 'cdashmu_prefill_user_registration_form',
            'nonce': nonce,
            'business_id': business_id,
        };
        // fill in the form
        $.post(url, data, function (response) {
            $("#business_id").val(response.business_id);
            $("#bus_name").val(response.business_name);
        });
    });
});