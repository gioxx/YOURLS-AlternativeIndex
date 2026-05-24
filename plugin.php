<?php
/*
Plugin Name: YOURLS Alternative Index
Plugin URI: https://github.com/gioxx/YOURLS-AlternativeIndex
Description: Transform the unused YOURLS root page into a Linktree-style profile page with social links, featured content, and custom branding.
Version: 1.0.0
Author: Gioxx
Author URI: https://gioxx.org
Text Domain: yourls-alternative-index
Domain Path: /languages
*/

if ( !defined( 'YOURLS_ABSPATH' ) ) die();

define( 'YAI_VERSION',             '1.0.0' );
define( 'YAI_GITHUB_OWNER',        'gioxx' );
define( 'YAI_GITHUB_REPO',         'YOURLS-AlternativeIndex' );
define( 'YAI_GITHUB_REPO_URL',     'https://github.com/' . YAI_GITHUB_OWNER . '/' . YAI_GITHUB_REPO );
define( 'YAI_GITHUB_RELEASES_URL', YAI_GITHUB_REPO_URL . '/releases/latest' );
define( 'YAI_GITHUB_API_URL',      'https://api.github.com/repos/' . YAI_GITHUB_OWNER . '/' . YAI_GITHUB_REPO . '/releases/latest' );

define( 'YAI_HTACCESS',        YOURLS_ABSPATH . '/.htaccess' );
define( 'YAI_HTACCESS_MARKER', '# BEGIN YAI-root' );
define( 'YAI_HTACCESS_RULE',   "# BEGIN YAI-root\nRewriteRule ^$ yourls-loader.php [L]\n# END YAI-root" );
define( 'YAI_INDEX',           YOURLS_ABSPATH . '/index.php' );
define( 'YAI_INDEX_BACKUP',    YOURLS_ABSPATH . '/index.php.yai-bak' );
define( 'YAI_INDEX_MARKER',    '/* YAI-managed-entry */' );
define( 'YAI_PLUGIN_DIR',      dirname( __FILE__ ) );
define( 'YAI_UPLOAD_DIR',      YOURLS_ABSPATH . '/user/uploads/alternative-index' );
define( 'YAI_UPLOAD_URL',      rtrim( YOURLS_SITE, '/' ) . '/user/uploads/alternative-index' );

$yai_inc = YAI_PLUGIN_DIR . '/inc/';
require_once $yai_inc . 'helpers.php';
require_once $yai_inc . 'htaccess.php';
require_once $yai_inc . 'update-check.php';
require_once $yai_inc . 'public-page.php';
require_once $yai_inc . 'admin-page.php';

yourls_add_action( 'admin_notices',                       'yai_show_update_notice' );
yourls_add_filter( 'plugin_page_title_alternative_index', 'yai_page_title_with_badge' );
