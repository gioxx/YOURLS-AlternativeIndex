<?php

function yai_remote_get( $url ) {
    $ch = curl_init();
    curl_setopt_array( $ch, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT      => 'YOURLS-AlternativeIndex/' . YAI_VERSION,
        CURLOPT_TIMEOUT        => 5,
    ] );
    $response  = curl_exec( $ch );
    $http_code = curl_getinfo( $ch, CURLINFO_HTTP_CODE );
    curl_close( $ch );
    if ( $http_code !== 200 || $response === false ) return null;
    return json_decode( $response, true );
}

function yai_sanitize_text_field( $str ) {
    return strip_tags( trim( $str ) );
}

function yai_asset_url( $relative_path ) {
    $relative_path = ltrim( (string) $relative_path, '/' );
    $plugin_dir    = YAI_PLUGIN_DIR;

    if ( function_exists( 'yourls_plugin_url' ) ) {
        return rtrim( (string) yourls_plugin_url( $plugin_dir ), '/' ) . '/' . $relative_path;
    }

    if ( defined( 'YOURLS_PLUGINDIRURL' ) ) {
        $slug = basename( $plugin_dir );
        return rtrim( (string) YOURLS_PLUGINDIRURL, '/' ) . '/' . $slug . '/' . $relative_path;
    }

    if ( defined( 'YOURLS_SITE' ) && defined( 'YOURLS_ABSPATH' ) ) {
        $rel = str_replace( '\\', '/', str_replace( (string) YOURLS_ABSPATH, '', $plugin_dir ) );
        $rel = trim( $rel, '/' );
        return rtrim( (string) YOURLS_SITE, '/' ) . '/' . $rel . '/' . $relative_path;
    }

    return '';
}

/**
 * Returns: null          — no file selected (caller keeps existing value)
 *          'ERR:<reason>' — specific failure
 *          string         — filename on success
 */
function yai_handle_upload( $field = 'yai_avatar_upload', $max_size = 2097152 ) {
    if ( empty( $_FILES[ $field ]['tmp_name'] ) ) return null;
    $file = $_FILES[ $field ];

    if ( $file['error'] !== UPLOAD_ERR_OK ) {
        $php_errors = [
            UPLOAD_ERR_INI_SIZE   => 'file exceeds the server upload_max_filesize limit',
            UPLOAD_ERR_FORM_SIZE  => 'file exceeds the form MAX_FILE_SIZE limit',
            UPLOAD_ERR_PARTIAL    => 'file was only partially uploaded',
            UPLOAD_ERR_NO_TMP_DIR => 'no temporary folder available on the server',
            UPLOAD_ERR_CANT_WRITE => 'failed to write file to disk',
            UPLOAD_ERR_EXTENSION  => 'upload blocked by a PHP extension',
        ];
        return 'ERR:' . ( $php_errors[ $file['error'] ] ?? 'PHP upload error ' . $file['error'] );
    }

    if ( $file['size'] > $max_size ) {
        return 'ERR:file exceeds the ' . round( $max_size / 1048576, 0 ) . ' MB size limit';
    }

    $mime = false;
    if ( class_exists( 'finfo' ) ) {
        $fi   = new finfo( FILEINFO_MIME_TYPE );
        $mime = $fi->file( $file['tmp_name'] );
    }
    if ( $mime === false && function_exists( 'mime_content_type' ) ) {
        $mime = mime_content_type( $file['tmp_name'] );
    }
    if ( $mime === 'image/jpg' ) $mime = 'image/jpeg';

    $exts = [ 'image/jpeg' => 'jpg', 'image/png' => 'png', 'image/gif' => 'gif', 'image/webp' => 'webp' ];
    if ( !$mime || !isset( $exts[ $mime ] ) ) {
        return 'ERR:file type not allowed (detected: ' . ( $mime ?: 'unknown' ) . ')';
    }

    if ( !is_dir( YAI_UPLOAD_DIR ) ) {
        if ( !mkdir( YAI_UPLOAD_DIR, 0755, true ) ) {
            return 'ERR:could not create uploads directory — check folder permissions on ' . YAI_UPLOAD_DIR;
        }
    }
    if ( !is_writable( YAI_UPLOAD_DIR ) ) {
        return 'ERR:uploads directory is not writable — check folder permissions on ' . YAI_UPLOAD_DIR;
    }

    $filename = 'upload_' . bin2hex( random_bytes( 8 ) ) . '.' . $exts[ $mime ];
    $dest     = YAI_UPLOAD_DIR . '/' . $filename;
    if ( !move_uploaded_file( $file['tmp_name'], $dest ) ) {
        return 'ERR:move_uploaded_file failed — check PHP open_basedir or safe_mode restrictions';
    }

    return $filename;
}
