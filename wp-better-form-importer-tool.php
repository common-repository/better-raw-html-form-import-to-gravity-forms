<?php
/*

* WP Better Form Importer *

*/

/* LOAD SCRIPTS AND STYLES */

wp_enqueue_script( 'jquery-ui-core' );
wp_enqueue_script( 'jquery-ui-draggable' );
wp_enqueue_script( 'jquery-ui-sortable' );
wp_enqueue_script( 'jquery-ui-slider' );
wp_enqueue_script( 'wpfi-admin-js', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), '1.0', true );
wp_enqueue_style( 'wpfi-admin-css', plugin_dir_url( __FILE__ ) . 'css/admin.css' );
wp_enqueue_style( 'jqueryui-css', plugin_dir_url( __FILE__ ) . 'css/jquery-ui.theme.min.css' );

if ( !is_plugin_active( 'gravityforms/gravityforms.php' ) ) {
    echo '<h2>Gravity Forms Must Be Installed & Active</h2>';
}
?>
<div class="wrap">
    <h2>WP Better Form Importer</h2>
    <p>Make sure Gravity Forms API is enabled in the plugin settings.</p>
    <?php
    if( isset( $_POST['htmlForm'] ) ) { ?>
        <form action="" method="POST">
        <table>
            <tr><td>Form Title</td><td><input type="text" id="wpfi_form_title" /></td></tr>
            <tr><td>Form Description</td><td><textarea id="wpfi_form_desc"></textarea></td></tr>
        </table>
        <?php
        echo '<input type="hidden" id="gf_api_url" value="' . site_url() . '/gravityformsapi/forms?_gf_json_nonce=' . wp_create_nonce( 'gf_api' ) . '" />';

        $importedForm = '';
        $stripAllLabels = false;
        if( isset( $_REQUEST['stripLabels'] ) ) {
            $stripAllLabels = true;
        }
        include_once( 'functions.php' );

        /* GATHER OUR FORM ELEMENTS */
        $dom = new DOMDocument;
        $dom->loadHTML( stripcslashes( $_REQUEST['htmlForm'] ) );
        $importedForm = '<div id="wpfi_editForm"><section id="wpfi_form_head">';
        $importedForm .= '<div>Keep</div><div><strong>Input Label</strong></div><div><strong>Input Name</strong></div><div><strong>Input Value</strong></div></section><ul>';
        $importedForm .= parseTable( $dom, $stripAllLabels );
        $importedForm .= '</ul></div>';
        echo $importedForm; ?>
        <p><input type="button" id="import_form_btn" value="Import Form to Gravity Forms" /></p>
        </form><?php
    } else {

    ?>

    <form action="" method="POST">
        <p>
            Copy HTML Form Code Here<br />
            Ideally, copy from &lt;form&gt; to &lt;/form&gt; only
        </p>
        <p>
            <textarea name="htmlForm" rows="20" cols="70"></textarea></p>
            <p><input type="checkbox" name="stripLabels" /> Check here to strip all labels</p>
            <p><input type="submit" value="Parse Form" />
        </p>
    </form>

    <?php } ?>
</div>