<?php

function yai_fetch_latest_release() {
    static $release = null;
    if ( $release === null ) {
        $response = yai_remote_get( YAI_GITHUB_API_URL );
        $release  = ( $response && isset( $response['tag_name'] ) ) ? $response : false;
    }
    return $release ?: null;
}

function yai_show_update_notice() {
    $release = yai_fetch_latest_release();
    if ( !$release ) return;
    $latest = ltrim( $release['tag_name'], 'v' );
    if ( version_compare( $latest, YAI_VERSION, '>' ) ) {
        static $style_printed = false;
        if ( !$style_printed ) {
            echo '<style>.yai-update-notice{display:block;width:100%;box-sizing:border-box;padding:14px 18px!important;margin:0 0 20px!important;border-left:4px solid #8ec5f7!important;border-bottom:2px solid #8ec5f7!important;border-radius:0!important;}</style>';
            $style_printed = true;
        }
        echo '<div class="notice notice-info yai-update-notice">&#x1F195; <strong>YOURLS Alternative Index</strong>: New version available: <strong>' . $latest . '</strong>! <a href="' . $release['html_url'] . '" target="_blank">View details on GitHub</a></div>';
    }
}

function yai_page_title_with_badge( $title ) {
    $release = yai_fetch_latest_release();
    if ( !$release ) return $title;
    $latest = ltrim( $release['tag_name'], 'v' );
    return version_compare( $latest, YAI_VERSION, '>' )
        ? $title . ' <span class="yai-update-badge">Update Available</span>'
        : $title;
}
