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
        echo '<div class="notice notice-info">&#x1F195; <strong>YOURLS Alternative Index</strong>: New version available: <strong>' . $latest . '</strong>! <a href="' . $release['html_url'] . '" target="_blank">View on GitHub</a></div>';
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
