<?php
/*
Plugin name: Download Theme | Plugin | WC products zip from dashboard
Plugin URI : https://wp-master.ir
Description: download themes | plugins and products from dashboard as Zip file
Author: wp-master.ir
Author URI: https://wp-master.ir
Version: 0.7
Text Domain: wdpfa
Domain Path: /languages
 */

/**
 * Check if WooCommerce is active
 **/
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    define('wdpfa_url', plugin_dir_url(__FILE__));
    define('wdpfa_dir', dirname(__FILE__) . DIRECTORY_SEPARATOR);
    load_plugin_textdomain('wdpfa', false, dirname(plugin_basename(__FILE__)) . DIRECTORY_SEPARATOR . 'languages');
    function wdpfa_get_dll_link($post_id)
    {
        global $wpdb;
        //make product
        $product = new WC_Product($post_id);

        //is downloadable?
        if (!$product->is_downloadable()) {
            return false;
        }

        $downloads = $product->get_downloads();
//        $downloads = $product->get_files();

        if (!empty($downloads)) {
            return $downloads;
        }

        return $downloads;
    }

    // ADD NEW COLUMN
    function wdpfa_columns_head($defaults)
    {
        $defaults['dll_file'] = __('Dll', 'wdpfa');
        return $defaults;
    }

    function wdpfa_generate_dll_buttons($post_ID)
    {
        $downloads = wdpfa_get_dll_link($post_ID);
        if (is_array($downloads)) {
            foreach ($downloads as $dll) {
                $file = explode('wp-content/', $dll['file']);
                $file = str_replace(array('/'), array(DIRECTORY_SEPARATOR), ABSPATH . 'wp-content' . DIRECTORY_SEPARATOR . $file[1]);
                $nonce = wp_create_nonce('wdpfa-nonce');
                echo '<a href="' . admin_url('?_wpnonce=' . $nonce . '&wdpfa_requested_file_path=' . $file) . '" class="button-primary dll-link-btn" data-dll-dir="' . $file . '"><span style="vertical-align:middle;">⬇️</span> ' . $dll['name'] . '</a>';
            }

            ?>
            <img class="ajax-loader" src="<?php echo wdpfa_url . '/ajax.gif'; ?>">
            <?php
        }
    }

    function wdpfa_columns_content($column_name, $post_ID)
    {
        $s = get_post_status($post_ID);
        if ($s != 'publish') {
            return;
        }

        if ($column_name == 'dll_file') {
            wdpfa_generate_dll_buttons($post_ID);
        }
    }

    add_filter('manage_product_posts_columns', 'wdpfa_columns_head');
    add_action('manage_product_posts_custom_column', 'wdpfa_columns_content', 10, 2);

    /*--------------- Admin scripts ---------------*/
    add_action('admin_enqueue_scripts', 'wdpfa_scripts');
    function wdpfa_scripts($hook)
    {
        if ($hook == 'edit.php' or $hook == 'post.php') {
            wp_enqueue_script('wdpfa_admin_js', wdpfa_url . 'admin.min.js', array('jquery'), '0.1', false);
            wp_enqueue_style('wdpfa_admin_css', wdpfa_url . 'admin.min.css', false, '0.1', false);
        }
    }


    /**
     * Register meta box(es).
     */
    function wdpfa_register_meta_boxes()
    {
        add_meta_box('dll-product-meta-box-id', __('Download box', 'wdpfa'), 'wdpfa_metabox_display_callback', 'product', 'normal',
            'high');
    }

    add_action('add_meta_boxes', 'wdpfa_register_meta_boxes');

    /**
     * Meta box display callback.
     *
     * @param WP_Post $post Current post object.
     */
    function wdpfa_metabox_display_callback($post)
    {
        $post_id = $post->ID;
        echo '<p class="dll_file">';
        wdpfa_generate_dll_buttons($post_id);
        echo '</p>';
    }

}

/**
 * Check if easy-digital-downloads is active
 **/
if (in_array('easy-digital-downloads/easy-digital-downloads.php', apply_filters('active_plugins', get_option('active_plugins')))) {

    if (!defined('wdpfa_edd_url')) {
        define('wdpfa_edd_url', plugin_dir_url(__FILE__));
        define('wdpfa_edd_dir', dirname(__FILE__) . DIRECTORY_SEPARATOR);
        load_plugin_textdomain('wdpfa', false, dirname(plugin_basename(__FILE__)) . DIRECTORY_SEPARATOR . 'languages');

    }
    function wdpfa_edd_get_dll_link($post_id)
    {
        $downloads = get_post_meta($post_id, 'edd_download_files', true);
        if (!$downloads) return;

        return $downloads;
    }

    // ADD NEW COLUMN
    function wdpfa_edd_columns_head($defaults)
    {
        $defaults['dll_file'] = __('Dll', 'wdpfa');
        return $defaults;
    }

    function wdpfa_edd_generate_dll_buttons($post_ID)
    {
        $downloads = wdpfa_edd_get_dll_link($post_ID);

        if (is_array($downloads)) {
            foreach ($downloads as $dll) {
                $file = explode('wp-content/', $dll['file']);
                $file = str_replace(array('/'), array(DIRECTORY_SEPARATOR), ABSPATH . 'wp-content' . DIRECTORY_SEPARATOR . $file[1]);
                $nonce = wp_create_nonce('wdpfa-nonce');
                echo '<a href="' . admin_url('?_wpnonce=' . $nonce . '&wdpfa_requested_file_path=' . $file) . '" class="button-primary dll-link-btn" data-dll-dir="' . $file . '"><span style="vertical-align:middle;">⬇️</span> ' . $dll['name'] . '</a>';
            }

            ?>
            <img class="ajax-loader" src="<?php echo wdpfa_edd_url . '/ajax.gif'; ?>">
            <?php
        }
    }

    function wdpfa_edd_columns_content($column_name, $post_ID)
    {
        $s = get_post_status($post_ID);
        if ($s != 'publish') {
            return;
        }

        if ($column_name == 'dll_file') {
            wdpfa_edd_generate_dll_buttons($post_ID);
        }
    }

    add_filter('manage_download_posts_columns', 'wdpfa_edd_columns_head');
    add_action('manage_download_posts_custom_column', 'wdpfa_edd_columns_content', 10, 2);

    /*--------------- Admin scripts ---------------*/
    add_action('admin_enqueue_scripts', 'wdpfa_edd_scripts');
    function wdpfa_edd_scripts($hook)
    {
        if ($hook == 'edit.php' or $hook == 'post.php') {
            wp_enqueue_script('wdpfa_edd_admin_js', wdpfa_edd_url . 'admin.min.js', array('jquery'), '0.1', false);
            wp_enqueue_style('wdpfa_edd_admin_css', wdpfa_edd_url . 'admin.min.css', false, '0.1', false);
        }
    }


    /**
     * Register meta box(es).
     */
    function wdpfa_edd_register_meta_boxes()
    {
        add_meta_box('dll-product-meta-box-id', __('Download box', 'wdpfa'), 'wdpfa_edd_metabox_display_callback', 'download', 'normal',
            'high');
    }

    add_action('add_meta_boxes', 'wdpfa_edd_register_meta_boxes');

    /**
     * Meta box display callback.
     *
     * @param WP_Post $post Current post object.
     */
    function wdpfa_edd_metabox_display_callback($post)
    {
        $post_id = $post->ID;
        echo '<p class="dll_file">';
        wdpfa_edd_generate_dll_buttons($post_id);
        echo '</p>';
    }

}


/**
 * for plugins
 */
if (is_admin()) {
    if (!function_exists('get_plugins')) {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
    }
    $plugins = get_plugins();
    foreach ($plugins as $plugin_file => $plugin_data) {
        add_filter('plugin_action_links_' . $plugin_file, 'wdpfa_plugins_add_action_links', 10, 4);

    }
    function wdpfa_plugins_add_action_links($actions, $plugin_file, $plugin_data, $context)
    {
        $file = str_replace(array('/'), array(DIRECTORY_SEPARATOR), ABSPATH . 'wp-content' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . $plugin_file);
        $nonce = wp_create_nonce('wdpfa-nonce');
        $mylinks = array(
            '<a href="' . admin_url('?_wpnonce=' . $nonce . '&wdpfa_requested_file_type=plugin&wdpfa_requested_file_path=' . $file) . '" class="button-primary dll-link-btn" data-dll-dir="' . $file . '"><span style="vertical-align:middle;">⬇️</span> ' . 'Dwonload' . '</a>');
        return array_merge($actions, $mylinks);
    }


}


/**
 * for themes
 * thanks to:
 * https://plugins.svn.wordpress.org/wp-downloader/trunk/wp-downloader.php
 */
add_action('admin_footer-themes.php', 'wdpfa_footer_script', PHP_INT_MAX);
function wdpfa_footer_script()
{
    // Download url
    $query = build_query(array(
        'wdpfa_requested_file_type' => 'theme',
        'wdpfa_requested_file_path' => '_obj_'));
    $url = wp_nonce_url(admin_url('?' . $query), 'wdpfa-nonce');
    // Label used for download links
    $label = '⬇️ ' . __('Download');
    // Current theme
    $current_theme = get_stylesheet();

    $script_template = '<script type="text/javascript" id="wdpfa">
        (function($){
            var url = "%s",
                label = "%s",
                current = "%s",
                button = \'<a class="button button-primary download hide-if-no-js" href="\' + url + \'">\' + label + \'</a>\';
            
            
            $(window).load(function(){          
                // For current theme in wordpress <3.8
                $("#current-theme .theme-options").after(\'<div class="theme-options"><a href="\' + url.replace("_obj_", current) + \'">\' + label + \'</a></div>\');

                // Add download button for each theme on the themes page
                $("#wpbody .theme .theme-actions .load-customize").each(function(i, e){
                    var btn = $(button),
                        $e = $(e),
                        href = $e.prop("href");

                    btn.prop("href", url.replace("_obj_", href.replace(/.*theme=(.*)(&|$)/, "$1")));

                    $e.parent().append(btn);
                });
            });

            // Modify single theme template to add the download button
            var d = $("#tmpl-theme-single").html(),
                ar = new RegExp(\'(<div class="active-theme">)(([\n\t]*(<#|<a).*[\n\t]*)*)(</div>)\', "mi");
                ir = new RegExp(\'(<div class="inactive-theme">)(([\n\t]*(<#|<a).*[\n\t]*)*)(</div>)\', "mi");

            d = d.replace(ar, "$1$2" + button + "$5");
            d = d.replace(ir, "$1$2" + button + "$5");

            $("#tmpl-theme-single").html(d);

            $(document).on("click", "a.button.download", function(e){
                e.preventDefault();
                var $this = $(this),
                    href = $(this).parent().find(".load-customize").attr("href"),
                    theme;

                theme = href.replace(/.*theme=(.*)(&|$)/, "$1");
                href = url.replace("_obj_", theme).replace(new RegExp("&amp;", "g"), "&");
                
                window.location = href;
            });
        }(jQuery))
    </script>';

    // Print javascript
    printf($script_template, $url, $label, $current_theme);
}


add_action('init', 'wdpfa_fetch_file', -9999999999);
function wdpfa_fetch_file()
{

    if (!isset($_REQUEST['wdpfa_requested_file_path'])) {
        return;
    }
    if (!function_exists('wp_get_current_user')) {
        include ABSPATH . "wp-includes/pluggable.php";
    }
    if (!current_user_can('manage_options')) {
        die('Forbidden');
    }
    if (!(isset($_GET['_wpnonce']) && wp_verify_nonce($_GET['_wpnonce'], 'wdpfa-nonce'))) {
        die('Forbidden');

    }
    if (isset($_REQUEST['wdpfa_requested_file_type']) && $_REQUEST['wdpfa_requested_file_type'] == 'plugin') {
        /* handle plugin */
        $plugin_dir = dirname($_REQUEST['wdpfa_requested_file_path']);
        wdpfa_zipFile($plugin_dir, str_replace('.php', '', basename(dirname($_REQUEST['wdpfa_requested_file_path']))) . '.zip', false);
        die();
    }

    if (isset($_REQUEST['wdpfa_requested_file_type']) && $_REQUEST['wdpfa_requested_file_type'] == 'theme') {
        /* handle plugin */
        $theme_name = sanitize_text_field($_REQUEST['wdpfa_requested_file_path']);
        $wp_theme = wp_get_theme($theme_name);
        $theme_dir = $wp_theme->get_template_directory();
        wdpfa_zipFile($theme_dir, basename($theme_dir) . '.zip', false);
        die();
    }

    $wdpfa_requested_file_path = $_REQUEST['wdpfa_requested_file_path'];
    if (empty($wdpfa_requested_file_path)) {
        return;
    }


    error_reporting(0); //Errors may corrupt download
    ob_start();
    $file_url = $wdpfa_requested_file_path;
    header('Content-Description: File Transfer');
    header('Content-Type: application/octet-stream');
    header('Expires: 0');
    header('Cache-Control: must-revalidate');
    header('Pragma: public');
    header("Content-disposition: attachment; filename=\"" . basename($file_url) . "\"");
    ob_clean();
    ob_end_flush();
    readfile($file_url); // do the double-download-dance (dirty but worky)
    die();
}


/**
 * Makes zip from folder
 * @author https://stackoverflow.com/a/17585672/1287812
 */
function wdpfa_zipFile($source, $destination, $flag = '')
{
    $tmp_file = tempnam(WP_CONTENT_DIR, '');

    if (!extension_loaded('zip')) {
        if (!class_exists('PclZip') && file_exists(ABSPATH . 'wp-admin/includes/class-pclzip.php')) {
            // Load class file if it's not loaded yet
            include ABSPATH . 'wp-admin/includes/class-pclzip.php';
        }
        if (!class_exists('PclZip')) {
            die('Zip extension not loaded');
        }

        $archive = new PclZip($tmp_file);
        // Add entire folder to the archive
        $archive->add($source, PCLZIP_OPT_REMOVE_PATH, dirname($source));
    } else {
        $zip = new ZipArchive();
        if (!$zip->open($tmp_file, ZIPARCHIVE::CREATE)) {
            return false;
        }

        $source = str_replace('\\', '/', realpath($source));
        if ($flag) {
            $flag = basename($source) . '/';
            //$zip->addEmptyDir(basename($source) . '/');
        }

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($source), RecursiveIteratorIterator::SELF_FIRST);
            foreach ($files as $file) {
                $file = str_replace('\\', '/', realpath($file));

                if (is_dir($file) === true) {
                    $src = str_replace($source . '/', '', $flag . $file . '/');
                    if (WP_PLUGIN_DIR . '/' !== $src) # Workaround, as it was creating a strange empty folder like /www_dev/dev.plugins/wp-content/plugins/
                        $zip->addEmptyDir($src);
                } else if (is_file($file) === true) {
                    $src = str_replace($source . '/', '', $flag . $file);
                    $zip->addFromString($src, file_get_contents($file));
                }
            }
        } else if (is_file($source) === true) {
            $zip->addFromString($flag . basename($source), file_get_contents($source));
        }

        $tt = $zip->close();
    }


    if (file_exists($tmp_file)) {
        // push to download the zip

        if (!function_exists('wp_get_current_user') && file_exists(ABSPATH . "wp-includes/pluggable.php")) {
            include ABSPATH . "wp-includes/pluggable.php";
        }
        if (function_exists('current_user_can')) {
            if (!current_user_can('manage_options')) {
                return;
            }
        }

        error_reporting(0); //Errors may corrupt download
        ob_start();
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header("Content-disposition: attachment; filename=\"" . $destination . "\"");
        ob_clean();
        ob_end_flush();
        readfile($tmp_file); // do the double-download-dance (dirty but worky)
        // remove zip file is exists in temp path
        unlink($tmp_file);

        die();
        exit();
    } else {
        echo $tt;
        die();
    }
}
