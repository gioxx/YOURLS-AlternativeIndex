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
        $url = yourls_esc_url( isset( $release['html_url'] ) ? $release['html_url'] : YAI_GITHUB_RELEASES_URL );
        echo '<div class="notice notice-info yai-update-notice">&#x1F195; <strong>YOURLS Alternative Index</strong>: '
            . sprintf( yourls__( 'New version available: %s!', 'yourls-alternative-index' ), '<strong>' . htmlspecialchars( $latest, ENT_QUOTES, 'UTF-8' ) . '</strong>' )
            . ' <a href="' . $url . '" target="_blank">' . yourls__( 'View details on GitHub', 'yourls-alternative-index' ) . '</a></div>';
    }
}

function yai_page_title_with_badge( $title ) {
    return $title;
}

function yai_has_update() {
    $release = yai_fetch_latest_release();
    if ( !$release ) return false;
    return version_compare( ltrim( $release['tag_name'], 'v' ), YAI_VERSION, '>' );
}
