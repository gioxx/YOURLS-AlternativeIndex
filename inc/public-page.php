<?php

yourls_add_action( 'plugins_loaded', 'yai_intercept', 99 );
function yai_intercept() {
    if ( defined( 'YOURLS_ADMIN' )      && YOURLS_ADMIN      ) return;
    if ( defined( 'YOURLS_DOING_API' )  && YOURLS_DOING_API  ) return;
    if ( defined( 'YOURLS_DOING_AJAX' ) && YOURLS_DOING_AJAX ) return;
    if ( !yourls_get_option( 'yai_enabled' ) ) return;

    $keyword = function_exists( 'yourls_get_request' ) ? yourls_get_request() : null;
    if ( $keyword !== '' && $keyword !== null && $keyword !== false ) return;

    yai_serve_page();
    die();
}

function yai_serve_page() {
    $name           = yourls_get_option( 'yai_profile_name' ) ?: 'My Profile';
    $tagline        = yourls_get_option( 'yai_tagline' ) ?: '';
    $page_title     = yourls_get_option( 'yai_page_title' ) ?: $name;
    $bg_color       = yourls_get_option( 'yai_bg_color' ) ?: '#1a1a2e';
    $accent_color   = yourls_get_option( 'yai_accent_color' ) ?: '#e94560';
    $text_color     = yourls_get_option( 'yai_text_color' ) ?: '#ffffff';

    $card_transparency_raw = yourls_get_option( 'yai_card_transparency' );
    $card_transparency = ( $card_transparency_raw === false || $card_transparency_raw === null || $card_transparency_raw === '' )
        ? 55
        : (int) $card_transparency_raw;
    if ( $card_transparency < 0 || $card_transparency > 100 ) $card_transparency = 55;

    $social_links   = json_decode( yourls_get_option( 'yai_social_links' ) ?: '[]', true ) ?: [];
    $featured_links = json_decode( yourls_get_option( 'yai_featured_links' ) ?: '[]', true ) ?: [];
    $custom_css     = yourls_get_option( 'yai_custom_css' ) ?: '';
    $raw_powered    = yourls_get_option( 'yai_show_powered_by' );
    $show_powered   = ( $raw_powered === false ) ? true : (bool) $raw_powered;

    $avatar_mode  = yourls_get_option( 'yai_avatar_mode' ) ?: 'url';
    $avatar_url   = yourls_get_option( 'yai_avatar_url' ) ?: '';
    $avatar_email = yourls_get_option( 'yai_avatar_email' ) ?: '';

    $bg_image_mode = yourls_get_option( 'yai_bg_image_mode' ) ?: 'none';
    $bg_image_url  = yourls_get_option( 'yai_bg_image_url' ) ?: '';
    if ( $bg_image_mode === 'upload' && $bg_image_url ) {
        $effective_bg_image = YAI_UPLOAD_URL . '/' . basename( $bg_image_url );
    } elseif ( $bg_image_mode === 'url' && $bg_image_url ) {
        $effective_bg_image = $bg_image_url;
    } else {
        $effective_bg_image = '';
    }
    $safe_bg_image = filter_var( $effective_bg_image, FILTER_VALIDATE_URL ) ? $effective_bg_image : '';

    if ( $avatar_mode === 'none' ) {
        $effective_avatar = '';
    } elseif ( $avatar_mode === 'gravatar' && $avatar_email ) {
        $effective_avatar = 'https://www.gravatar.com/avatar/' . md5( strtolower( trim( $avatar_email ) ) ) . '?s=200&d=mp';
    } elseif ( $avatar_mode === 'upload' && $avatar_url ) {
        $effective_avatar = YAI_UPLOAD_URL . '/' . basename( $avatar_url );
    } else {
        $effective_avatar = $avatar_url;
    }

    $bg_color     = preg_match( '/^#[0-9a-fA-F]{3,8}$/', $bg_color )     ? $bg_color     : '#1a1a2e';
    $accent_color = preg_match( '/^#[0-9a-fA-F]{3,8}$/', $accent_color ) ? $accent_color : '#e94560';
    $text_color   = preg_match( '/^#[0-9a-fA-F]{3,8}$/', $text_color )   ? $text_color   : '#ffffff';

    $safe_name    = htmlspecialchars( $name,       ENT_QUOTES, 'UTF-8' );
    $safe_tagline = htmlspecialchars( $tagline,    ENT_QUOTES, 'UTF-8' );
    $safe_title   = htmlspecialchars( $page_title, ENT_QUOTES, 'UTF-8' );
    $safe_avatar  = filter_var( $effective_avatar, FILTER_VALIDATE_URL ) ? htmlspecialchars( $effective_avatar, ENT_QUOTES ) : '';

    header( 'Content-Type: text/html; charset=UTF-8' );
    echo '<!DOCTYPE html><html lang="en"><head>';
    echo '<meta charset="UTF-8">';
    echo '<meta name="viewport" content="width=device-width,initial-scale=1">';
    echo '<title>' . $safe_title . '</title>';
    echo '<style>';
    yai_print_page_style( $bg_color, $accent_color, $text_color, $safe_bg_image, $card_transparency );
    if ( $custom_css ) echo $custom_css;
    echo '</style></head><body><div class="yai-page"><div class="yai-card">';

    if ( $safe_avatar ) {
        echo '<img class="yai-avatar" src="' . $safe_avatar . '" alt="' . $safe_name . '">';
    }

    echo '<h1 class="yai-name">' . $safe_name . '</h1>';
    if ( $safe_tagline ) {
        echo '<p class="yai-tagline">' . $safe_tagline . '</p>';
    }

    if ( !empty( $social_links ) ) {
        echo '<div class="yai-social">';
        foreach ( $social_links as $sl ) {
            if ( empty( $sl['platform'] ) || empty( $sl['url'] ) ) continue;
            $platform = strtolower( trim( $sl['platform'] ) );
            $raw_url  = $sl['url'];
            if ( $platform === 'email' ) {
                $email = preg_replace( '#^mailto:#i', '', $raw_url );
                if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) continue;
                $url = htmlspecialchars( 'mailto:' . $email, ENT_QUOTES );
            } else {
                if ( !filter_var( $raw_url, FILTER_VALIDATE_URL ) ) continue;
                $url = htmlspecialchars( $raw_url, ENT_QUOTES );
            }
            $label  = htmlspecialchars( ucwords( str_replace( [ '-', '_' ], ' ', $platform ) ), ENT_QUOTES );
            $target = ( $platform === 'email' ) ? '' : ' target="_blank" rel="noopener noreferrer"';
            echo '<a href="' . $url . '" class="yai-social-icon"' . $target . ' aria-label="' . $label . '" title="' . $label . '">';
            echo yai_get_social_svg( $platform );
            echo '</a>';
        }
        echo '</div>';
    }

    if ( !empty( $featured_links ) ) {
        echo '<div class="yai-links">';
        foreach ( $featured_links as $fl ) {
            if ( empty( $fl['title'] ) || empty( $fl['url'] ) ) continue;
            if ( !filter_var( $fl['url'], FILTER_VALIDATE_URL ) ) continue;
            $emoji  = !empty( $fl['emoji'] ) ? '<span class="yai-link-emoji">' . htmlspecialchars( $fl['emoji'], ENT_QUOTES ) . '</span>' : '';
            $ftitle = htmlspecialchars( $fl['title'], ENT_QUOTES );
            $furl   = htmlspecialchars( $fl['url'], ENT_QUOTES );
            echo '<a href="' . $furl . '" class="yai-link" target="_blank" rel="noopener noreferrer">' . $emoji . '<span>' . $ftitle . '</span></a>';
        }
        echo '</div>';
    }

    if ( $show_powered ) {
        echo '<p class="yai-powered">Powered by <a href="' . YAI_GITHUB_REPO_URL . '" target="_blank" rel="noopener noreferrer">YOURLS Alternative Index</a></p>';
    }

    echo '</div></div></body></html>';
}

function yai_print_page_style( $bg, $accent, $text, $bg_image = '', $card_transparency = 55 ) {
    $safe_bg_image_css = $bg_image ? str_replace( [ '"', "'", '\\', "\n" ], '', $bg_image ) : '';
    $card_transparency = is_numeric( $card_transparency ) ? (int) $card_transparency : 55;
    if ( $card_transparency < 0 ) $card_transparency = 0;
    if ( $card_transparency > 100 ) $card_transparency = 100;
    $card_visibility = ( 100 - $card_transparency ) / 100;
    $card_fill_alpha = number_format( $card_visibility * 0.45, 2, '.', '' );
    $card_blur       = (int) round( 22 * $card_visibility );
    $card_border     = number_format( 0.13 * $card_visibility, 2, '.', '' );
    $card_shadow     = number_format( 0.55 * $card_visibility, 2, '.', '' );
    $card_inset      = number_format( 0.08 * $card_visibility, 2, '.', '' );

    echo "
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
body { min-height: 100vh; background-color: {$bg}; color: {$text}; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; display: flex; align-items: center; justify-content: center; padding: 2rem 1rem; }
.yai-page { width: 100%; display: flex; justify-content: center; position: relative; }
.yai-card { width: 100%; max-width: 480px; display: flex; flex-direction: column; align-items: center; gap: 0; position: relative; z-index: 1; }
.yai-avatar { width: 96px; height: 96px; border-radius: 50%; object-fit: cover; border: 3px solid {$accent}; margin-bottom: 1rem; }
.yai-name { font-size: 1.5rem; font-weight: 800; text-align: center; margin-bottom: .35rem; }
.yai-tagline { font-size: .9rem; opacity: .75; text-align: center; margin-bottom: 1.25rem; line-height: 1.5; max-width: 380px; }
.yai-social { display: flex; flex-wrap: wrap; gap: .6rem; justify-content: center; margin-bottom: 1.5rem; }
.yai-social-icon { display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 50%; background: rgba(255,255,255,.1); color: {$text}; transition: background .2s, transform .2s; }
.yai-social-icon:hover { background: {$accent}; transform: translateY(-2px); }
.yai-social-icon svg { width: 20px; height: 20px; fill: currentColor; }
.yai-links { display: flex; flex-direction: column; gap: .75rem; width: 100%; margin-bottom: 1.5rem; }
.yai-link { display: flex; align-items: center; justify-content: center; gap: .6rem; width: 100%; padding: .85rem 1.5rem; border-radius: 50px; background: rgba(255,255,255,.1); color: {$text}; text-decoration: none; font-weight: 600; font-size: .95rem; transition: background .2s, transform .2s, box-shadow .2s; border: 1px solid rgba(255,255,255,.08); }
.yai-link:hover { background: {$accent}; transform: translateY(-2px); box-shadow: 0 6px 20px rgba(0,0,0,.25); }
.yai-link-emoji { font-size: 1.1rem; line-height: 1; }
.yai-powered { font-size: .72rem; opacity: .45; text-align: center; }
.yai-powered a { color: inherit; }
";

    if ( $safe_bg_image_css ) {
        echo "
body { background-image: url(\"{$safe_bg_image_css}\"); background-size: cover; background-position: center; background-attachment: fixed; }
body::before { content: ''; position: fixed; inset: 0; background: rgba(0,0,0,.38); z-index: 0; pointer-events: none; }
.yai-card { background: rgba(15,15,25,{$card_fill_alpha}); backdrop-filter: blur({$card_blur}px) saturate(160%); -webkit-backdrop-filter: blur({$card_blur}px) saturate(160%); border: 1px solid rgba(255,255,255,{$card_border}); border-radius: 28px; padding: 2.75rem 2.25rem; box-shadow: 0 8px 48px rgba(0,0,0,{$card_shadow}), inset 0 1px 0 rgba(255,255,255,{$card_inset}); }
.yai-social-icon { background: rgba(255,255,255,.12); border: 1px solid rgba(255,255,255,.08); }
.yai-link { background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.13); }
.yai-link:hover { background: {$accent}; border-color: {$accent}; }
";
    }
}

function yai_get_social_svg( $platform ) {
    $icons = [
        'website'   => '<svg viewBox="0 0 24 24"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/></svg>',
        'email'     => '<svg viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-2 .9-2 2v12c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>',
        'github'    => '<svg viewBox="0 0 24 24"><path d="M12 .297c-6.63 0-12 5.373-12 12 0 5.303 3.438 9.8 8.205 11.385.6.113.82-.258.82-.577 0-.285-.01-1.04-.015-2.04-3.338.724-4.042-1.61-4.042-1.61C4.422 18.07 3.633 17.7 3.633 17.7c-1.087-.744.084-.729.084-.729 1.205.084 1.838 1.236 1.838 1.236 1.07 1.835 2.809 1.305 3.495.998.108-.776.417-1.305.76-1.605-2.665-.3-5.466-1.332-5.466-5.93 0-1.31.465-2.38 1.235-3.22-.135-.303-.54-1.523.105-3.176 0 0 1.005-.322 3.3 1.23.96-.267 1.98-.399 3-.405 1.02.006 2.04.138 3 .405 2.28-1.552 3.285-1.23 3.285-1.23.645 1.653.24 2.873.12 3.176.765.84 1.23 1.91 1.23 3.22 0 4.61-2.805 5.625-5.475 5.92.42.36.81 1.096.81 2.22 0 1.606-.015 2.896-.015 3.286 0 .315.21.69.825.57C20.565 22.092 24 17.592 24 12.297c0-6.627-5.373-12-12-12"/></svg>',
        'twitter'   => '<svg viewBox="0 0 24 24"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 7.184ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/></svg>',
        'x'         => '<svg viewBox="0 0 24 24"><path d="M18.901 1.153h3.68l-8.04 9.19L24 22.846h-7.406l-5.8-7.584-6.638 7.584H.474l8.6-9.83L0 1.154h7.594l5.243 7.184ZM17.61 20.644h2.039L6.486 3.24H4.298Z"/></svg>',
        'instagram' => '<svg viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>',
        'facebook'  => '<svg viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>',
        'linkedin'  => '<svg viewBox="0 0 24 24"><path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433a2.062 2.062 0 0 1-2.063-2.065 2.064 2.064 0 1 1 2.063 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/></svg>',
        'youtube'   => '<svg viewBox="0 0 24 24"><path d="M23.495 6.205a3.007 3.007 0 0 0-2.088-2.088c-1.87-.501-9.396-.501-9.396-.501s-7.507-.01-9.396.501A3.007 3.007 0 0 0 .527 6.205a31.247 31.247 0 0 0-.522 5.805 31.247 31.247 0 0 0 .522 5.783 3.007 3.007 0 0 0 2.088 2.088c1.868.502 9.396.502 9.396.502s7.506 0 9.396-.502a3.007 3.007 0 0 0 2.088-2.088 31.247 31.247 0 0 0 .5-5.783 31.247 31.247 0 0 0-.5-5.805zM9.609 15.601V8.408l6.264 3.602z"/></svg>',
        'tiktok'    => '<svg viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>',
        'mastodon'  => '<svg viewBox="0 0 24 24"><path d="M23.268 5.313c-.35-2.578-2.617-4.61-5.304-5.004C17.51.242 15.792 0 11.813 0h-.03c-3.98 0-4.835.242-5.288.309C3.882.692 1.496 2.518.917 5.127.64 6.412.61 7.837.661 9.143c.074 1.874.088 3.745.26 5.611.118 1.24.325 2.47.62 3.68.55 2.237 2.777 4.098 4.96 4.857 2.336.792 4.849.923 7.256.38.265-.061.527-.132.786-.213.585-.184 1.27-.39 1.774-.753a.057.057 0 0 0 .023-.043v-1.809a.052.052 0 0 0-.066-.051c-1.517.363-3.072.546-4.632.546-2.873 0-3.641-1.365-3.674-1.818a5.593 5.593 0 0 1-.319-1.433.053.053 0 0 1 .066-.054c1.517.363 3.072.546 4.632.546.376 0 .75 0 1.125-.01 1.57-.044 3.224-.124 4.768-.422.038-.008.077-.015.11-.024 2.435-.464 4.753-1.92 4.989-5.604.008-.145.03-1.52.03-1.67.002-.512.167-3.63-.024-5.545zm-3.748 9.195h-2.561V8.29c0-1.309-.55-1.976-1.67-1.976-1.23 0-1.846.79-1.846 2.35v3.403h-2.546V8.663c0-1.56-.617-2.35-1.848-2.35-1.112 0-1.668.668-1.67 1.977v6.218H4.822V8.102c0-1.31.337-2.35 1.011-3.12.696-.77 1.608-1.164 2.74-1.164 1.311 0 2.302.5 2.962 1.498l.638 1.06.638-1.06c.66-.999 1.65-1.498 2.96-1.498 1.13 0 2.043.395 2.74 1.164.675.77 1.012 1.81 1.012 3.12z"/></svg>',
        'bluesky'   => '<svg viewBox="0 0 24 24"><path d="M12 10.8c-1.087-2.114-4.046-6.053-6.798-7.995C2.566.944 1.561 1.266.902 1.565.139 1.908 0 3.08 0 3.768c0 .69.378 5.65.624 6.479.815 2.736 3.713 3.66 6.383 3.364.136-.02.275-.039.415-.056-.138.022-.276.04-.415.056-3.912.58-7.387 2.005-2.83 7.078 5.013 5.19 6.87-1.113 7.823-4.308.953 3.195 2.05 9.271 7.733 4.308 4.267-4.308 1.172-6.498-2.74-7.078a8.741 8.741 0 0 1-.415-.056c.14.017.279.036.415.056 2.67.297 5.568-.628 6.383-3.364.246-.828.624-5.79.624-6.478 0-.69-.139-1.861-.902-2.206-.659-.298-1.664-.62-4.3 1.24C16.046 4.748 13.087 8.687 12 10.8z"/></svg>',
        'threads'   => '<svg viewBox="0 0 24 24"><path d="M12.186 24h-.007c-3.581-.024-6.334-1.205-8.184-3.509C2.35 18.44 1.5 15.586 1.472 12.01v-.017c.03-3.579.858-6.43 2.46-8.47C5.56 1.312 7.874.025 10.702 0h.03c2.184.017 4.146.613 5.83 1.771a11.37 11.37 0 0 1 3.157 3.233l-2.417 1.657a8.5 8.5 0 0 0-2.328-2.35c-1.173-.788-2.656-1.196-4.27-1.207-2.091.017-3.788.817-5.046 2.378-1.276 1.58-1.925 3.856-1.951 6.764v.013c.026 2.91.675 5.188 1.951 6.77 1.257 1.56 2.954 2.355 5.046 2.372 2.017-.019 3.447-.597 4.518-1.818.972-1.103 1.525-2.544 1.65-4.284-.636.258-1.306.394-1.98.404h-.004c-2.003 0-3.542-.754-4.503-2.182-.899-1.338-1.052-2.914-.435-4.441C9.44 6.973 11.14 5.78 13.38 5.78c.195 0 .39.012.583.035 1.72.205 3.004.906 3.817 2.085.83 1.199 1.124 2.772.872 4.685-.262 1.98-.97 3.6-2.103 4.816C15.358 18.648 13.927 24 12.186 24zm.623-13.293c-.856 0-1.456.316-1.826.966-.407.714-.39 1.598.05 2.27.41.626 1.026.928 1.882.928h.002c.67-.01 1.335-.186 1.96-.523-.033-1.01-.26-2.573-1.163-3.46a2.017 2.017 0 0 0-.905-.181z"/></svg>',
        'pinterest' => '<svg viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174-.105-.949-.2-2.405.042-3.441.218-.937 1.407-5.965 1.407-5.965s-.359-.719-.359-1.782c0-1.668.967-2.914 2.171-2.914 1.023 0 1.518.769 1.518 1.69 0 1.029-.655 2.568-.994 3.995-.283 1.194.599 2.169 1.777 2.169 2.133 0 3.772-2.249 3.772-5.495 0-2.873-2.064-4.882-5.012-4.882-3.414 0-5.418 2.561-5.418 5.207 0 1.031.397 2.138.893 2.738a.36.36 0 0 1 .083.345l-.333 1.36c-.053.22-.174.267-.402.161-1.499-.698-2.436-2.889-2.436-4.649 0-3.785 2.75-7.262 7.929-7.262 4.163 0 7.398 2.967 7.398 6.931 0 4.136-2.607 7.464-6.227 7.464-1.216 0-2.359-.632-2.75-1.378l-.748 2.853c-.271 1.043-1.002 2.35-1.492 3.146C9.57 23.812 10.763 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0z"/></svg>',
        'twitch'    => '<svg viewBox="0 0 24 24"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z"/></svg>',
        'discord'   => '<svg viewBox="0 0 24 24"><path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057c.001.024.012.044.03.06a19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/></svg>',
        'telegram'  => '<svg viewBox="0 0 24 24"><path d="M11.944 0A12 12 0 0 0 0 12a12 12 0 0 0 12 12 12 12 0 0 0 12-12A12 12 0 0 0 12 0a12 12 0 0 0-.056 0zm4.962 7.224c.1-.002.321.023.465.14a.506.506 0 0 1 .171.325c.016.093.036.306.02.472-.18 1.898-.962 6.502-1.36 8.627-.168.9-.499 1.201-.82 1.23-.696.065-1.225-.46-1.9-.902-1.056-.693-1.653-1.124-2.678-1.8-1.185-.78-.417-1.21.258-1.91.177-.184 3.247-2.977 3.307-3.23.007-.032.014-.15-.056-.212s-.174-.041-.249-.024c-.106.024-1.793 1.14-5.061 3.345-.48.33-.913.49-1.302.48-.428-.008-1.252-.241-1.865-.44-.752-.245-1.349-.374-1.297-.789.027-.216.325-.437.893-.663 3.498-1.524 5.83-2.529 6.998-3.014 3.332-1.386 4.025-1.627 4.476-1.635z"/></svg>',
        'whatsapp'  => '<svg viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 0 1-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 0 1 2.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0 0 12.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 0 0 5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 0 0-3.48-8.413z"/></svg>',
    ];

    if ( isset( $icons[ $platform ] ) ) return $icons[ $platform ];

    $letter = strtoupper( substr( $platform, 0, 1 ) );
    return '<svg viewBox="0 0 24 24"><circle cx="12" cy="12" r="12" fill="currentColor" opacity=".2"/><text x="12" y="16" text-anchor="middle" font-size="12" font-weight="bold" fill="currentColor">' . $letter . '</text></svg>';
}
