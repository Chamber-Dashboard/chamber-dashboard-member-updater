<?php
function cdashmu_business_edit_form_block(){
    if ( function_exists( 'register_block_type' ) ) {
        register_block_type(
            'cdash-bd-blocks/mu-edit-form', [
                'render_callback' => 'cdash_business_edit_form_block_callback',
            ]
        );
    }
}
add_action( 'init', 'cdashmu_business_edit_form_block' );

function cdash_business_edit_form_block_callback(){
    $business_edit_form = cdashmu_business_update_form_shortcode();

    return $business_edit_form;
}
?>