<?php

yourls_add_action( 'plugins_loaded', 'yai_boot' );
function yai_boot() {
    yai_load_textdomain();
    yourls_register_plugin_page( 'alternative_index', yourls__( 'Alternative Index', 'yourls-alternative-index' ), 'yai_config_page' );
}

function yai_load_textdomain() {
    $locale = yourls_get_locale();
    $domain = 'yourls-alternative-index';
    $path   = YAI_PLUGIN_DIR . '/languages/';
    if ( file_exists( $path . "{$domain}-{$locale}.mo" ) ) {
        yourls_load_textdomain( $domain, $path . "{$domain}-{$locale}.mo" );
    } elseif ( file_exists( $path . "{$domain}-{$locale}.po" ) ) {
        yourls_load_textdomain( $domain, $path . "{$domain}-{$locale}.po" );
    }
}

function yai_enqueue_admin_assets( $social_links, $featured_links ) {
    $css_url  = yai_asset_url( 'assets/admin.css' );
    if ( $css_url !== '' ) {
        $ver      = YAI_VERSION;
        $css_file = YAI_PLUGIN_DIR . '/assets/admin.css';
        if ( file_exists( $css_file ) ) $ver = (string) filemtime( $css_file );
        echo '<link rel="stylesheet" href="' . htmlspecialchars( $css_url, ENT_QUOTES ) . '?v=' . rawurlencode( $ver ) . '">';
    }

    $platforms = [ 'website','email','github','x','instagram','facebook','linkedin','youtube','tiktok','mastodon','bluesky','threads','pinterest','twitch','discord','telegram','whatsapp' ];
    echo '<script>window.YAI_Data=' . json_encode(
        [ 'platforms' => $platforms, 'social' => $social_links, 'featured' => $featured_links ],
        JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT
    ) . ';</script>';

    $js_url  = yai_asset_url( 'assets/admin.js' );
    if ( $js_url !== '' ) {
        $ver     = YAI_VERSION;
        $js_file = YAI_PLUGIN_DIR . '/assets/admin.js';
        if ( file_exists( $js_file ) ) $ver = (string) filemtime( $js_file );
        echo '<script src="' . htmlspecialchars( $js_url, ENT_QUOTES ) . '?v=' . rawurlencode( $ver ) . '"></script>';
    }
}

function yai_config_page() {
    $messages = [];

    if ( isset( $_POST['yai_save'] ) ) {
        yourls_verify_nonce( 'yai_config' );
        $result     = yai_save_settings();
        $messages[] = [ 'type' => $result['success'] ? 'success' : 'warning', 'text' => $result['text'] ];
    }

    if ( isset( $_POST['yai_reset'] ) ) {
        yourls_verify_nonce( 'yai_reset', $_POST['nonce_reset'] );
        yai_reset_settings();
        $messages[] = [ 'type' => 'warning', 'text' => yourls__( 'Settings reset to default.', 'yourls-alternative-index' ) ];
    }

    if ( isset( $_POST['yai_htaccess_remove'] ) ) {
        yourls_verify_nonce( 'yai_config' );
        yai_htaccess_remove_rule();
        $messages[] = [ 'type' => 'success', 'text' => yourls__( 'Legacy .htaccess rule removed.', 'yourls-alternative-index' ) ];
    }

    $enabled        = (bool) yourls_get_option( 'yai_enabled' );
    $name           = yourls_get_option( 'yai_profile_name' ) ?: '';
    $tagline        = yourls_get_option( 'yai_tagline' ) ?: '';
    $avatar_mode    = yourls_get_option( 'yai_avatar_mode' ) ?: 'url';
    $avatar_url     = yourls_get_option( 'yai_avatar_url' ) ?: '';
    $avatar_email   = yourls_get_option( 'yai_avatar_email' ) ?: '';
    $page_title     = yourls_get_option( 'yai_page_title' ) ?: '';
    $bg_color       = yourls_get_option( 'yai_bg_color' ) ?: '#1a1a2e';
    $accent_color   = yourls_get_option( 'yai_accent_color' ) ?: '#e94560';
    $text_color     = yourls_get_option( 'yai_text_color' ) ?: '#ffffff';
    $bg_image_mode  = yourls_get_option( 'yai_bg_image_mode' ) ?: 'none';
    $bg_image_url   = yourls_get_option( 'yai_bg_image_url' ) ?: '';

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

    $nonce_config = yourls_create_nonce( 'yai_config' );
    $nonce_reset  = yourls_create_nonce( 'yai_reset' );

    yai_enqueue_admin_assets( $social_links, $featured_links );
    yai_show_update_notice();

    echo '<div class="yai-header">';
    $page_heading = htmlspecialchars( yourls_apply_filters( 'plugin_page_title_alternative_index', 'YOURLS Alternative Index' ), ENT_QUOTES, 'UTF-8' );
    $update_badge = yai_has_update() ? ' <span class="yai-update-badge">' . yourls__( 'Update Available', 'yourls-alternative-index' ) . '</span>' : '';
    echo '<h2 class="yai-title">&#127968; <span class="yai-title-text">' . $page_heading . $update_badge . '</span></h2>';
    echo '<p class="yai-version">' . sprintf( yourls__( 'Version: %s', 'yourls-alternative-index' ), YAI_VERSION ) . '</p>';
    echo '</div>';

    foreach ( $messages as $msg ) {
        echo '<div class="notice notice-' . $msg['type'] . '"><p>' . $msg['text'] . '</p></div>';
    }

    echo '<form method="post" enctype="multipart/form-data" class="yai-form" id="yai-form">';
    echo '<input type="hidden" name="nonce" value="' . $nonce_config . '">';

    // ── Enable/disable ──
    echo '<div class="yai-panel">';
    echo '<h3 class="yai-heading">&#9889; ' . yourls__( 'Plugin Status', 'yourls-alternative-index' ) . '</h3>';
    echo '<div class="yai-panel-body">';
    echo '<div class="yai-check-row">';
    echo '<label class="yai-toggle-label">';
    echo '<input type="checkbox" name="yai_enabled" value="1" class="yai-toggle-cb"' . ( $enabled ? ' checked' : '' ) . '>';
    echo '<span class="yai-toggle"></span>';
    echo '<span>' . yourls__( 'Enable Alternative Index page', 'yourls-alternative-index' ) . '</span>';
    echo '</label>';
    echo '<small>' . yourls__( 'When enabled, visitors to the YOURLS root URL will see the profile page below instead of a 403 error.', 'yourls-alternative-index' ) . '</small>';
    echo '</div>';

    $index_ok  = yai_index_is_custom();
    $idx_write = is_writable( dirname( YAI_INDEX ) )
              || ( file_exists( YAI_INDEX ) && is_writable( YAI_INDEX ) );

    if ( $index_ok ) {
        echo '<div class="yai-htaccess-ok">';
        echo '&#10003; <strong>' . yourls__( 'index.php managed', 'yourls-alternative-index' ) . '</strong> &mdash; ';
        echo yourls__( 'the root URL loads through YOURLS correctly. Save with the toggle OFF to restore the original.', 'yourls-alternative-index' );
        echo '</div>';
    } elseif ( $enabled ) {
        echo '<div class="yai-htaccess-warn">';
        echo '<strong>&#9888; ' . yourls__( 'index.php not yet patched', 'yourls-alternative-index' ) . '</strong> &mdash; ';
        if ( $idx_write ) {
            echo yourls__( 'save settings once to apply the fix automatically.', 'yourls-alternative-index' );
        } else {
            echo sprintf( yourls__( '%s is not writable by PHP. Create or replace it manually with:', 'yourls-alternative-index' ), '<code>index.php</code>' );
            echo '<pre class="yai-code">&lt;?php' . "\n" . 'require_once __DIR__ . \'/yourls-loader.php\';</pre>';
        }
        echo '</div>';
    }

    if ( yai_htaccess_has_rule() ) {
        echo '<div class="yai-htaccess-warn yai-htaccess-warn-mt">';
        echo '&#9432; ' . sprintf( yourls__( 'The %s rewrite rule from an earlier version is still present. It is now redundant', 'yourls-alternative-index' ), '<code>.htaccess</code>' );
        echo ' &mdash; <button type="submit" form="yai-ht-remove" class="button yai-btn-sm">' . yourls__( 'Remove it', 'yourls-alternative-index' ) . '</button>';
        echo '</div>';
    }

    echo '</div></div>';

    // ── Profile ──
    echo '<div class="yai-panel">';
    echo '<h3 class="yai-heading">&#128100; ' . yourls__( 'Profile', 'yourls-alternative-index' ) . '</h3>';
    echo '<div class="yai-panel-body">';

    echo '<div class="yai-row">';
    echo '<label for="yai_profile_name">' . yourls__( 'Display Name', 'yourls-alternative-index' ) . '</label>';
    echo '<input type="text" name="yai_profile_name" id="yai_profile_name" value="' . yourls_esc_attr( $name ) . '" placeholder="Jane Doe">';
    echo '</div>';

    echo '<div class="yai-row">';
    echo '<label for="yai_tagline">' . yourls__( 'Tagline / Bio', 'yourls-alternative-index' ) . '</label>';
    echo '<small>' . yourls__( 'Short description shown below your name.', 'yourls-alternative-index' ) . '</small>';
    echo '<input type="text" name="yai_tagline" id="yai_tagline" value="' . yourls_esc_attr( $tagline ) . '" placeholder="Photographer &bull; Traveler &bull; Coffee addict">';
    echo '</div>';

    echo '<div class="yai-row">';
    echo '<label>' . yourls__( 'Avatar', 'yourls-alternative-index' ) . '</label>';
    echo '<div class="yai-avatar-modes">';
    $avatar_mode_labels = [
        'url'      => yourls__( 'External URL', 'yourls-alternative-index' ),
        'gravatar' => 'Gravatar',
        'upload'   => yourls__( 'Upload image', 'yourls-alternative-index' ),
        'none'     => yourls__( 'No avatar', 'yourls-alternative-index' ),
    ];
    foreach ( $avatar_mode_labels as $mode_key => $mode_label ) {
        $checked = ( $avatar_mode === $mode_key ) ? ' checked' : '';
        echo '<label class="yai-avatar-mode-label"><input type="radio" name="yai_avatar_mode" value="' . $mode_key . '"' . $checked . '> ' . $mode_label . '</label>';
    }
    echo '</div>';

    echo '<div class="yai-avatar-panel' . ( $avatar_mode !== 'url' ? ' yai-panel--hidden' : '' ) . '" id="yai-avatar-url-panel">';
    echo '<small>' . yourls__( 'Direct link to your profile picture (square images work best).', 'yourls-alternative-index' ) . '</small>';
    echo '<input type="text" name="yai_avatar_url" id="yai_avatar_url" value="' . yourls_esc_attr( $avatar_url ) . '" placeholder="https://example.com/photo.jpg">';
    echo '<img id="yai-avatar-url-preview" class="yai-img-preview' . ( ($avatar_mode === 'url' && $avatar_url) ? '' : ' yai-panel--hidden' ) . '" src="' . ( ($avatar_mode === 'url' && $avatar_url) ? yourls_esc_attr( $avatar_url ) : '' ) . '" alt="Avatar preview">';
    echo '<div id="yai-avatar-url-size-warning" class="yai-size-warning yai-panel--hidden"></div>';
    echo '</div>';

    echo '<div class="yai-avatar-panel' . ( $avatar_mode !== 'gravatar' ? ' yai-panel--hidden' : '' ) . '" id="yai-avatar-gravatar-panel">';
    echo '<small>' . sprintf( yourls__( 'Enter your email address to use your %s profile picture.', 'yourls-alternative-index' ), '<a href="https://gravatar.com" target="_blank" rel="noopener noreferrer">Gravatar</a>' ) . '</small>';
    echo '<input type="email" name="yai_avatar_email" id="yai_avatar_email" value="' . yourls_esc_attr( $avatar_email ) . '" placeholder="you@example.com">';
    echo '</div>';

    echo '<div class="yai-avatar-panel' . ( $avatar_mode !== 'upload' ? ' yai-panel--hidden' : '' ) . '" id="yai-avatar-upload-panel">';
    echo '<small>' . yourls__( 'Upload a JPG, PNG, GIF, or WebP image (max 2 MB). Square images work best.', 'yourls-alternative-index' ) . '</small>';
    if ( $avatar_mode === 'upload' && $avatar_url ) {
        $current_img = htmlspecialchars( YAI_UPLOAD_URL . '/' . basename( $avatar_url ), ENT_QUOTES );
        echo '<div class="yai-avatar-current"><img src="' . $current_img . '" alt="Current avatar" class="yai-avatar-thumb"> <span class="yai-avatar-filename">' . htmlspecialchars( basename( $avatar_url ), ENT_QUOTES ) . '</span></div>';
    }
    echo '<input type="file" name="yai_avatar_upload" id="yai_avatar_upload" accept="image/jpeg,image/png,image/gif,image/webp">';
    echo '</div>';

    echo '<div class="yai-avatar-panel' . ( $avatar_mode !== 'none' ? ' yai-panel--hidden' : '' ) . '" id="yai-avatar-none-panel">';
    echo '<small>' . yourls__( 'No avatar will be shown on the profile page.', 'yourls-alternative-index' ) . '</small>';
    echo '</div>';

    echo '</div>';
    echo '</div></div>';

    // ── Appearance ──
    echo '<div class="yai-panel">';
    echo '<h3 class="yai-heading">&#127912; ' . yourls__( 'Appearance', 'yourls-alternative-index' ) . '</h3>';
    echo '<div class="yai-panel-body">';

    echo '<div class="yai-row">';
    echo '<label for="yai_page_title">' . yourls__( 'Browser Tab Title', 'yourls-alternative-index' ) . '</label>';
    echo '<small>' . yourls__( 'Defaults to your display name if left empty.', 'yourls-alternative-index' ) . '</small>';
    echo '<input type="text" name="yai_page_title" id="yai_page_title" value="' . yourls_esc_attr( $page_title ) . '" placeholder="Jane Doe &mdash; Links">';
    echo '</div>';

    echo '<div class="yai-color-items">';
    $color_fields = [
        'yai_bg_color'     => [ yourls__( 'Background Color', 'yourls-alternative-index' ),  yourls__( 'Page background.', 'yourls-alternative-index' ),           $bg_color ],
        'yai_accent_color' => [ yourls__( 'Accent Color', 'yourls-alternative-index' ),       yourls__( 'Buttons &amp; icon hover.', 'yourls-alternative-index' ),  $accent_color ],
        'yai_text_color'   => [ yourls__( 'Text Color', 'yourls-alternative-index' ),         yourls__( 'All text on the page.', 'yourls-alternative-index' ),      $text_color ],
    ];
    foreach ( $color_fields as $field_id => [ $label, $hint, $val ] ) {
        echo '<div class="yai-color-item">';
        echo '<label for="' . $field_id . '">' . $label . '</label>';
        if ( $hint ) echo '<small>' . $hint . '</small>';
        echo '<div class="yai-color-input-row">';
        echo '<input type="color" name="' . $field_id . '" id="' . $field_id . '" value="' . yourls_esc_attr( $val ) . '">';
        echo '<input type="text" class="yai-hex-input" maxlength="7" placeholder="#000000" value="' . yourls_esc_attr( $val ) . '" aria-label="' . $label . ' hex value">';
        echo '</div>';
        echo '</div>';
    }
    echo '</div>';

    echo '<div class="yai-row yai-row-mt">';
    echo '<label>' . yourls__( 'Background Image', 'yourls-alternative-index' ) . '</label>';
    echo '<small>' . yourls__( 'When set, a frosted-glass effect is applied to the profile card for a polished look.', 'yourls-alternative-index' ) . '</small>';
    echo '<div class="yai-avatar-modes">';
    $bg_mode_labels = [
        'none'   => yourls__( 'None', 'yourls-alternative-index' ),
        'url'    => yourls__( 'External URL', 'yourls-alternative-index' ),
        'upload' => yourls__( 'Upload image', 'yourls-alternative-index' ),
    ];
    foreach ( $bg_mode_labels as $mode_key => $mode_label ) {
        $checked = ( $bg_image_mode === $mode_key ) ? ' checked' : '';
        echo '<label class="yai-avatar-mode-label"><input type="radio" name="yai_bg_image_mode" value="' . $mode_key . '"' . $checked . '> ' . $mode_label . '</label>';
    }
    echo '</div>';

    echo '<div class="yai-bgimg-panel' . ( $bg_image_mode !== 'none' ? ' yai-panel--hidden' : '' ) . '" id="yai-bgimg-none-panel">';
    echo '<small>' . yourls__( 'No background image — the background color above will be used.', 'yourls-alternative-index' ) . '</small>';
    echo '</div>';

    echo '<div class="yai-bgimg-panel' . ( $bg_image_mode !== 'url' ? ' yai-panel--hidden' : '' ) . '" id="yai-bgimg-url-panel">';
    echo '<small>' . yourls__( 'Direct link to the background image. Wide landscape photos work best (e.g. Unsplash).', 'yourls-alternative-index' ) . '</small>';
    echo '<input type="text" name="yai_bg_image_url" id="yai_bg_image_url" value="' . yourls_esc_attr( $bg_image_mode === 'url' ? $bg_image_url : '' ) . '" placeholder="https://example.com/background.jpg">';
    echo '<img id="yai-bgimg-url-preview" class="yai-img-preview' . ( ($bg_image_mode === 'url' && $bg_image_url) ? '' : ' yai-panel--hidden' ) . '" src="' . ( ($bg_image_mode === 'url' && $bg_image_url) ? yourls_esc_attr( $bg_image_url ) : '' ) . '" alt="Background preview">';
    echo '<div id="yai-bgimg-url-size-warning" class="yai-size-warning yai-panel--hidden"></div>';
    echo '</div>';

    echo '<div class="yai-bgimg-panel' . ( $bg_image_mode !== 'upload' ? ' yai-panel--hidden' : '' ) . '" id="yai-bgimg-upload-panel">';
    echo '<small>' . yourls__( 'Upload a JPG, PNG, GIF, or WebP image (max 5 MB). Wide landscape photos work best.', 'yourls-alternative-index' ) . '</small>';
    if ( $bg_image_mode === 'upload' && $bg_image_url ) {
        $current_bg = htmlspecialchars( YAI_UPLOAD_URL . '/' . basename( $bg_image_url ), ENT_QUOTES );
        echo '<div class="yai-bgimg-current"><img src="' . $current_bg . '" alt="Current background" class="yai-bgimg-thumb"> <span class="yai-avatar-filename">' . htmlspecialchars( basename( $bg_image_url ), ENT_QUOTES ) . '</span></div>';
    }
    echo '<input type="file" name="yai_bg_image_upload" id="yai_bg_image_upload" accept="image/jpeg,image/png,image/gif,image/webp">';
    echo '</div>';

    echo '<div class="yai-row yai-row-mt">';
    echo '<label for="yai_card_transparency">' . yourls__( 'Info Box Transparency (%)', 'yourls-alternative-index' ) . '</label>';
    echo '<small>' . yourls__( 'Used only when a background image is active. 0 keeps the current frosted look, 100 removes the glass effect and makes the box fully transparent.', 'yourls-alternative-index' ) . '</small>';
    echo '<input type="number" name="yai_card_transparency" id="yai_card_transparency" min="0" max="100" step="1" value="' . yourls_esc_attr( (string) $card_transparency ) . '">';
    echo '</div>';

    echo '</div>';
    echo '</div></div>';

    // ── Social Links ──
    echo '<div class="yai-panel">';
    echo '<h3 class="yai-heading">&#128279; ' . yourls__( 'Social Links', 'yourls-alternative-index' ) . '</h3>';
    echo '<div class="yai-panel-body">';
    echo '<small>' . yourls__( 'Social icons shown in a row below your tagline.', 'yourls-alternative-index' ) . '</small>';
    echo '<div class="yai-col-headers">';
    echo '<span class="yai-ch-platform">' . yourls__( 'Platform', 'yourls-alternative-index' ) . '</span>';
    echo '<span class="yai-ch-url">URL</span>';
    echo '<span class="yai-ch-spacer"></span>';
    echo '</div>';
    echo '<div id="yai-social-list"></div>';
    echo '<button type="button" class="button yai-add-btn" id="yai-add-social">' . yourls__( '+ Add Social Link', 'yourls-alternative-index' ) . '</button>';
    echo '<input type="hidden" name="yai_social_links" id="yai_social_links_input">';
    echo '</div></div>';

    // ── Featured Links ──
    echo '<div class="yai-panel">';
    echo '<h3 class="yai-heading">&#127775; ' . yourls__( 'Featured Links', 'yourls-alternative-index' ) . '</h3>';
    echo '<div class="yai-panel-body">';
    echo '<small>' . yourls__( 'Full-width link buttons shown below the social icons.', 'yourls-alternative-index' ) . '</small>';
    echo '<div class="yai-col-headers">';
    echo '<span class="yai-ch-emoji">' . yourls__( 'Emoji', 'yourls-alternative-index' ) . '</span>';
    echo '<span class="yai-ch-title">' . yourls__( 'Title', 'yourls-alternative-index' ) . '</span>';
    echo '<span class="yai-ch-featured-url">URL</span>';
    echo '<span class="yai-ch-spacer"></span>';
    echo '</div>';
    echo '<div id="yai-links-list"></div>';
    echo '<button type="button" class="button yai-add-btn" id="yai-add-link">' . yourls__( '+ Add Featured Link', 'yourls-alternative-index' ) . '</button>';
    echo '<input type="hidden" name="yai_featured_links" id="yai_featured_links_input">';
    echo '</div></div>';

    // ── Advanced ──
    echo '<div class="yai-panel">';
    echo '<h3 class="yai-heading">&#128295; ' . yourls__( 'Advanced', 'yourls-alternative-index' ) . '</h3>';
    echo '<div class="yai-panel-body">';

    echo '<div class="yai-row">';
    echo '<label for="yai_custom_css">' . yourls__( 'Custom CSS', 'yourls-alternative-index' ) . '</label>';
    echo '<small>' . sprintf( yourls__( 'Added inside a &lt;style&gt; tag on the public page. Target %s, %s, etc.', 'yourls-alternative-index' ), '<code>.yai-card</code>', '<code>.yai-link</code>' ) . '</small>';
    echo '<textarea name="yai_custom_css" id="yai_custom_css" rows="6" class="yai-custom-css-field">' . htmlspecialchars( $custom_css ) . '</textarea>';
    echo '</div>';

    echo '<div class="yai-check-row">';
    echo '<label class="yai-toggle-label">';
    echo '<input type="checkbox" name="yai_show_powered_by" value="1" class="yai-toggle-cb"' . ( $show_powered ? ' checked' : '' ) . '>';
    echo '<span class="yai-toggle"></span>';
    echo '<span>' . yourls__( 'Show &ldquo;Powered by YOURLS Alternative Index&rdquo; footer link', 'yourls-alternative-index' ) . '</span>';
    echo '</label>';
    echo '</div>';

    echo '</div></div>';

    // ── Actions ──
    echo '<div class="yai-actions">';
    echo '<button type="submit" name="yai_save" class="button">&#128190; ' . yourls__( 'Save Settings', 'yourls-alternative-index' ) . '</button>';
    $confirm_msg = yourls__( 'Reset all settings to default?', 'yourls-alternative-index' );
    echo '<button type="submit" name="yai_reset" class="button" onclick="return confirm(\'' . htmlspecialchars( $confirm_msg, ENT_QUOTES ) . '\');" formnovalidate>&#128260; ' . yourls__( 'Reset to Default', 'yourls-alternative-index' ) . '</button>';
    echo '<input type="hidden" name="nonce_reset" value="' . $nonce_reset . '">';
    if ( $enabled ) {
        echo '<a href="' . rtrim( YOURLS_SITE, '/' ) . '/" target="_blank" rel="noopener noreferrer" class="button">&#128279; ' . yourls__( 'Preview Page', 'yourls-alternative-index' ) . '</a>';
    }
    echo '</div>';

    echo '<div class="yai-footer">';
    echo '<div class="plugin-footer-top">';
    echo '<span>';
    echo '<a href="https://yourls.gioxx.org/plugins/alternative-index" target="_blank" rel="noopener noreferrer">&#127760; YOURLS Alternative Index</a>';
    echo ' &nbsp;&middot;&nbsp; ';
    echo '<a href="' . YAI_GITHUB_REPO_URL . '" target="_blank" rel="noopener noreferrer"><img src="https://github.githubassets.com/favicons/favicon.png" class="github-icon" alt="GitHub">GitHub</a>';
    echo '</span>';
    echo '<a href="#" onclick="window.scrollTo({top:0,behavior:\'smooth\'});return false;">&#8593; ' . yourls__( 'Back to top', 'yourls-alternative-index' ) . '</a>';
    echo '</div>';
    echo '<div>&#10084;&#65039; Lovingly developed by the usually-on-vacation brain cell of ';
    echo '<a href="https://github.com/gioxx" target="_blank" rel="noopener noreferrer">Gioxx</a> &ndash; ';
    echo '<a href="https://gioxx.org" target="_blank" rel="noopener noreferrer">Gioxx\'s Wall</a>';
    echo '</div>';
    echo '</div>';

    echo '</form>';

    echo '<form id="yai-ht-remove" method="post" class="yai-panel--hidden">';
    echo '<input type="hidden" name="nonce" value="' . $nonce_config . '">';
    echo '<input type="hidden" name="yai_htaccess_remove" value="1">';
    echo '</form>';
}

function yai_save_settings() {
    $enabled = isset( $_POST['yai_enabled'] ) ? 1 : 0;
    yourls_update_option( 'yai_enabled', $enabled );

    if ( $enabled ) {
        yai_index_apply();
    } else {
        yai_index_restore();
    }

    yourls_update_option( 'yai_profile_name', yai_sanitize_text_field( $_POST['yai_profile_name'] ?? '' ) );
    yourls_update_option( 'yai_tagline',      yai_sanitize_text_field( $_POST['yai_tagline'] ?? '' ) );
    yourls_update_option( 'yai_page_title',   yai_sanitize_text_field( $_POST['yai_page_title'] ?? '' ) );

    $avatar_mode = in_array( $_POST['yai_avatar_mode'] ?? '', [ 'url', 'gravatar', 'upload', 'none' ], true )
        ? $_POST['yai_avatar_mode']
        : 'url';
    yourls_update_option( 'yai_avatar_mode', $avatar_mode );

    if ( $avatar_mode === 'url' ) {
        $avatar = trim( $_POST['yai_avatar_url'] ?? '' );
        if ( $avatar && !filter_var( $avatar, FILTER_VALIDATE_URL ) ) {
            return [ 'success' => false, 'text' => yourls__( 'Avatar URL is not a valid URL.', 'yourls-alternative-index' ) ];
        }
        yourls_update_option( 'yai_avatar_url', $avatar );

    } elseif ( $avatar_mode === 'gravatar' ) {
        $email = trim( $_POST['yai_avatar_email'] ?? '' );
        if ( $email && !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) {
            return [ 'success' => false, 'text' => yourls__( 'Gravatar email is not a valid email address.', 'yourls-alternative-index' ) ];
        }
        yourls_update_option( 'yai_avatar_email', strtolower( $email ) );

    } elseif ( $avatar_mode === 'upload' ) {
        $uploaded = yai_handle_upload( 'yai_avatar_upload' );
        if ( is_string( $uploaded ) && strpos( $uploaded, 'ERR:' ) === 0 ) {
            return [ 'success' => false, 'text' => sprintf( yourls__( 'Avatar upload failed: %s', 'yourls-alternative-index' ), substr( $uploaded, 4 ) ) ];
        }
        if ( $uploaded !== null ) {
            yourls_update_option( 'yai_avatar_url', $uploaded );
        }
    }

    $bg_image_mode = in_array( $_POST['yai_bg_image_mode'] ?? '', [ 'none', 'url', 'upload' ], true )
        ? $_POST['yai_bg_image_mode']
        : 'none';
    yourls_update_option( 'yai_bg_image_mode', $bg_image_mode );

    if ( $bg_image_mode === 'url' ) {
        $bg_img = trim( $_POST['yai_bg_image_url'] ?? '' );
        if ( $bg_img && !filter_var( $bg_img, FILTER_VALIDATE_URL ) ) {
            return [ 'success' => false, 'text' => yourls__( 'Background image URL is not a valid URL.', 'yourls-alternative-index' ) ];
        }
        yourls_update_option( 'yai_bg_image_url', $bg_img );

    } elseif ( $bg_image_mode === 'upload' ) {
        $uploaded = yai_handle_upload( 'yai_bg_image_upload', 5 * 1024 * 1024 );
        if ( is_string( $uploaded ) && strpos( $uploaded, 'ERR:' ) === 0 ) {
            return [ 'success' => false, 'text' => sprintf( yourls__( 'Background image upload failed: %s', 'yourls-alternative-index' ), substr( $uploaded, 4 ) ) ];
        }
        if ( $uploaded !== null ) {
            yourls_update_option( 'yai_bg_image_url', $uploaded );
        }
    }

    foreach ( [ 'yai_bg_color', 'yai_accent_color', 'yai_text_color' ] as $key ) {
        $val = trim( $_POST[ $key ] ?? '' );
        if ( preg_match( '/^#[0-9a-fA-F]{3,8}$/', $val ) ) {
            yourls_update_option( $key, $val );
        }
    }

    $card_transparency = isset( $_POST['yai_card_transparency'] ) ? (int) $_POST['yai_card_transparency'] : 55;
    if ( $card_transparency < 0 ) $card_transparency = 0;
    if ( $card_transparency > 100 ) $card_transparency = 100;
    yourls_update_option( 'yai_card_transparency', $card_transparency );

    $social_raw = trim( $_POST['yai_social_links'] ?? '[]' );
    $social     = json_decode( $social_raw, true );
    if ( !is_array( $social ) ) $social = [];
    $social = array_values( array_filter( array_map( function ( $item ) {
        if ( empty( $item['platform'] ) || empty( $item['url'] ) ) return null;
        $platform = strtolower( trim( $item['platform'] ) );
        $url      = trim( $item['url'] );
        if ( $platform === 'email' ) {
            $email = preg_replace( '#^mailto:#i', '', $url );
            if ( !filter_var( $email, FILTER_VALIDATE_EMAIL ) ) return null;
            $url = 'mailto:' . strtolower( $email );
        } else {
            if ( !preg_match( '#^https?://#i', $url ) ) $url = 'https://' . $url;
            if ( !filter_var( $url, FILTER_VALIDATE_URL ) ) return null;
        }
        return [ 'platform' => yai_sanitize_text_field( $platform ), 'url' => $url ];
    }, $social ) ) );
    yourls_update_option( 'yai_social_links', json_encode( $social ) );

    $links_raw = trim( $_POST['yai_featured_links'] ?? '[]' );
    $links     = json_decode( $links_raw, true );
    if ( !is_array( $links ) ) $links = [];
    $links = array_values( array_filter( array_map( function ( $item ) {
        if ( empty( $item['title'] ) || empty( $item['url'] ) ) return null;
        $url = trim( $item['url'] );
        if ( !preg_match( '#^https?://#i', $url ) ) $url = 'https://' . $url;
        if ( !filter_var( $url, FILTER_VALIDATE_URL ) ) return null;
        return [
            'emoji' => mb_substr( trim( $item['emoji'] ?? '' ), 0, 4 ),
            'title' => yai_sanitize_text_field( $item['title'] ),
            'url'   => $url,
        ];
    }, $links ) ) );
    yourls_update_option( 'yai_featured_links', json_encode( $links ) );

    yourls_update_option( 'yai_custom_css',      $_POST['yai_custom_css'] ?? '' );
    yourls_update_option( 'yai_show_powered_by', isset( $_POST['yai_show_powered_by'] ) ? 1 : 0 );

    return [ 'success' => true, 'text' => yourls__( 'Settings saved successfully!', 'yourls-alternative-index' ) ];
}

function yai_reset_settings() {
    yai_index_restore();
    yai_htaccess_remove_rule();
    foreach ( [
        'yai_enabled', 'yai_profile_name', 'yai_tagline',
        'yai_avatar_mode', 'yai_avatar_url', 'yai_avatar_email',
        'yai_page_title', 'yai_bg_color', 'yai_accent_color', 'yai_text_color',
        'yai_bg_image_mode', 'yai_bg_image_url', 'yai_card_transparency',
        'yai_social_links', 'yai_featured_links', 'yai_custom_css', 'yai_show_powered_by',
    ] as $key ) {
        yourls_delete_option( $key );
    }
}
