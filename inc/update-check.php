<?php

function yai_fetch_latest_release() {
    static $cached_release = null;
    if ( $cached_release !== null ) return $cached_release ?: null;

    $cache = yourls_get_option( 'yai_update_cache' );
    if ( $cache && is_array( $cache ) && isset( $cache['checked_at'], $cache['latest_version'] ) ) {
        if ( ( time() - (int) $cache['checked_at'] ) < 6 * 3600 ) {
            $cached_release = [ 'tag_name' => $cache['latest_version'] ];
            return $cached_release;
        }
    }

    $response = yai_remote_get( YAI_GITHUB_API_URL );
    if ( $response && isset( $response['tag_name'] ) ) {
        yourls_update_option( 'yai_update_cache', [
            'checked_at'     => time(),
            'latest_version' => $response['tag_name'],
        ] );
        $cached_release = $response;
    } else {
        $cached_release = false;
    }
    return $cached_release ?: null;
}

function yai_show_update_notice() {
    $release = yai_fetch_latest_release();
    if ( !$release ) return;
    $latest = ltrim( $release['tag_name'], 'v' );
    if ( version_compare( $latest, YAI_VERSION, '>' ) ) {
        $url = yourls_esc_url( isset( $release['html_url'] ) ? $release['html_url'] : YAI_GITHUB_RELEASES_URL );
        echo '<div class="notice notice-info yai-update-notice">&#x1F195; <strong>YOURLS Alternative Index</strong>: New version available: <strong>' . htmlspecialchars( $latest, ENT_QUOTES, 'UTF-8' ) . '</strong>! <a href="' . $url . '" target="_blank">View details on GitHub</a></div>';
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
