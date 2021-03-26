import edit from './edit';

import { registerBlockType } from '@wordpress/blocks';
import { __ } from '@wordpress/i18n';
import { dateI18n, format, __experimentalGetSettings } from '@wordpress/date';
import { setState } from '@wordpress/compose';

 
registerBlockType( 'cdash-bd-blocks/mu-edit-form', {
    title: 'Business Edit Form',
    icon: 'list-view',
    category: 'cd-blocks',
    description: __('This block displays the business edit form.', 'cdashmu'),
    example: {
    },
    edit: edit,
    save(){
        //Rendering in PHP
        return null;
    },
} );