<?php
function cdashmu_member_registration_form_block(){
    if ( function_exists( 'register_block_type' ) ) {
        register_block_type(
            'cdash-bd-blocks/mu-registration-form', [
                'render_callback' => 'cdash_member_registration_form_block_callback',
            ]
        );
    }
}
add_action( 'init', 'cdashmu_member_registration_form_block' );

function cdash_member_registration_form_block_callback(){
    $member_registration_form = custom_registration_shortcode();

    return $member_registration_form;
}
?>