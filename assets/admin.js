(function () {
    'use strict';

    var data        = window.YAI_Data || { platforms: [], social: [], featured: [] };
    var platforms   = data.platforms;
    var socialData   = data.social;
    var featuredData = data.featured;

    function platformOptions(selected) {
        return platforms.map(function (p) {
            var sel   = (p === selected) ? ' selected' : '';
            var label = p === 'x' ? 'X (Twitter)' : p.charAt(0).toUpperCase() + p.slice(1);
            return '<option value="' + p + '"' + sel + '>' + label + '</option>';
        }).join('');
    }

    function makeSocialRow(item) {
        item = item || { platform: platforms[0], url: '' };
        var platform = item.platform || platforms[0];

        var row = document.createElement('div');
        row.className = 'yai-dynamic-row';
        var placeholder = platform === 'email' ? 'email@example.com' : 'https://...';
        row.innerHTML = '<select>' + platformOptions(platform) + '</select>'
            + '<input type="text" class="yai-social-url" placeholder="' + placeholder + '" value="' + escAttr(item.url || '') + '">'
            + '<button type="button" class="yai-rm-btn" title="Remove">&times;</button>';

        var sel = row.querySelector('select');
        var inp = row.querySelector('.yai-social-url');

        sel.addEventListener('change', function () {
            inp.placeholder = sel.value === 'email' ? 'email@example.com' : 'https://...';
            serializeSocial();
        });
        inp.addEventListener('input', serializeSocial);
        row.querySelector('.yai-rm-btn').addEventListener('click', function () { row.remove(); serializeSocial(); });
        return row;
    }

    function makeFeaturedRow(item) {
        item = item || { emoji: '', title: '', url: '' };
        var row = document.createElement('div');
        row.className = 'yai-dynamic-row';
        row.innerHTML = '<input type="text" class="yai-emoji-input" placeholder="🔗" maxlength="4" value="' + escAttr(item.emoji || '') + '" title="Emoji (optional)">'
            + '<input type="text" class="yai-featured-title" placeholder="Link title" value="' + escAttr(item.title || '') + '">'
            + '<input type="text" class="yai-featured-url" placeholder="https://" value="' + escAttr(item.url || '') + '">'
            + '<button type="button" class="yai-rm-btn" title="Remove">&times;</button>';
        row.querySelector('.yai-rm-btn').addEventListener('click', function () { row.remove(); serializeFeatured(); });
        row.querySelectorAll('input').forEach(function (i) { i.addEventListener('input', serializeFeatured); });
        return row;
    }

    function serializeSocial() {
        var rows = document.querySelectorAll('#yai-social-list .yai-dynamic-row');
        var d    = [];
        rows.forEach(function (row) {
            var sel = row.querySelector('select');
            var inp = row.querySelector('.yai-social-url');
            if (!sel || !inp) return;
            d.push({ platform: sel.value, url: inp.value.trim() });
        });
        document.getElementById('yai_social_links_input').value = JSON.stringify(d);
    }

    function serializeFeatured() {
        var rows = document.querySelectorAll('#yai-links-list .yai-dynamic-row');
        var d    = [];
        rows.forEach(function (row) {
            var inputs = row.querySelectorAll('input');
            if (inputs.length >= 3) {
                d.push({ emoji: inputs[0].value.trim(), title: inputs[1].value.trim(), url: inputs[2].value.trim() });
            }
        });
        document.getElementById('yai_featured_links_input').value = JSON.stringify(d);
    }

    function escAttr(s) {
        return String(s).replace(/&/g, '&amp;').replace(/"/g, '&quot;').replace(/'/g, '&#39;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
    }

    document.addEventListener('DOMContentLoaded', function () {
        var socialList   = document.getElementById('yai-social-list');
        var featuredList = document.getElementById('yai-links-list');

        socialData.forEach(function (item) { socialList.appendChild(makeSocialRow(item)); });
        featuredData.forEach(function (item) { featuredList.appendChild(makeFeaturedRow(item)); });

        serializeSocial();
        serializeFeatured();

        document.getElementById('yai-add-social').addEventListener('click', function () {
            socialList.appendChild(makeSocialRow(null));
            serializeSocial();
        });

        document.getElementById('yai-add-link').addEventListener('click', function () {
            featuredList.appendChild(makeFeaturedRow(null));
            serializeFeatured();
        });

        document.getElementById('yai-form').addEventListener('submit', function () {
            serializeSocial();
            serializeFeatured();
        });

        // Avatar mode toggle
        document.querySelectorAll('input[name="yai_avatar_mode"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.yai-avatar-panel').forEach(function (p) { p.style.display = 'none'; });
                var panel = document.getElementById('yai-avatar-' + radio.value + '-panel');
                if (panel) panel.style.display = '';
            });
        });

        // Background image mode toggle
        document.querySelectorAll('input[name="yai_bg_image_mode"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                document.querySelectorAll('.yai-bgimg-panel').forEach(function (p) { p.style.display = 'none'; });
                var panel = document.getElementById('yai-bgimg-' + radio.value + '-panel');
                if (panel) panel.style.display = '';
            });
        });

        // Color picker <-> hex input sync
        document.querySelectorAll('.yai-color-input-row').forEach(function (row) {
            var picker = row.querySelector('input[type="color"]');
            var hex    = row.querySelector('.yai-hex-input');
            if (!picker || !hex) return;
            picker.addEventListener('input', function () { hex.value = picker.value; });
            hex.addEventListener('input', function () {
                if (/^#[0-9a-fA-F]{6}$/.test(hex.value)) picker.value = hex.value;
            });
            hex.addEventListener('blur', function () {
                if (!/^#[0-9a-fA-F]{6}$/.test(hex.value)) hex.value = picker.value;
            });
        });
    });
})();
