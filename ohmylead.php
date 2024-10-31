<?php

/*
Plugin Name: Ohmylead Add-on Gravity Forms
Plugin URI: https://www.ohmylead.com
Description: Integrates Gravity Forms with Ohmylead, allowing form submissions to be automatically sent to your configured Sources.
Version: 1.0
Author: soulaimane bined
Text Domain: ohmyleadgravityforms
License: GPL-2.0+

------------------------------------------------------------------------
Copyright 2019 ohmylead

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.

*/

define( 'GF_SIMPLE_ADDON_VERSION', '2.0' );

add_action( 'gform_loaded', array( 'GF_Ohmylead_AddOn_Bootstrap', 'load' ), 5 );

class GF_Ohmylead_AddOn_Bootstrap {

    public static function load() {

        if ( ! method_exists( 'GFForms', 'include_addon_framework' ) ) {
            return;
        }

        require_once( 'class-gfohmylead.php' );

        GFAddOn::register( 'GFOhmyleadAddOn' );
    }

}