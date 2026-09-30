<?php
/** Small view helpers: icons, pills, form fields. */

function icon($name, $cls = '')
{
    static $p = array(
        'menu' => '<path d="M4 6h16M4 12h16M4 18h16"/>',
        'x' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'search' => '<circle cx="11" cy="11" r="7"/><path d="M20 20l-4-4"/>',
        'eye' => '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/>',
        'save' => '<path d="M5 3h11l3 3v15H5z"/><path d="M8 3v5h8V3M8 21v-7h8v7"/>',
        'send' => '<path d="M4 12l16-8-6 16-2-7z"/>',
        'check' => '<path d="M5 12l5 5 9-10"/>',
        'alert' => '<path d="M12 3l10 18H2z"/><path d="M12 10v5M12 18v.5"/>',
        'dot' => '<circle cx="12" cy="12" r="4"/>',
        'plus' => '<path d="M12 5v14M5 12h14"/>',
        'copy' => '<rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V5a1 1 0 00-1-1H5a1 1 0 00-1 1v10a1 1 0 001 1h3"/>',
        'trash' => '<path d="M4 7h16M9 7V4h6v3M6 7l1 13h10l1-13"/>',
        'up' => '<path d="M12 19V5M5 12l7-7 7 7"/>',
        'down' => '<path d="M12 5v14M5 12l7 7 7-7"/>',
        'grip' => '<circle cx="9" cy="6" r="1.2"/><circle cx="15" cy="6" r="1.2"/><circle cx="9" cy="12" r="1.2"/><circle cx="15" cy="12" r="1.2"/><circle cx="9" cy="18" r="1.2"/><circle cx="15" cy="18" r="1.2"/>',
        'image' => '<rect x="3" y="4" width="18" height="16" rx="2"/><circle cx="9" cy="10" r="2"/><path d="M21 17l-6-6-9 9"/>',
        'upload' => '<path d="M12 16V4M6 10l6-6 6 6M4 20h16"/>',
        'star' => '<path d="M12 3l2.8 5.8 6.2.9-4.5 4.4 1 6.2L12 17.4 6.5 20.3l1-6.2L3 9.7l6.2-.9z"/>',
        'crop' => '<path d="M6 2v16h16M2 6h16v16"/>',
        'lock' => '<rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 018 0v3"/>',
        'box' => '<path d="M3 7l9-4 9 4v10l-9 4-9-4z"/><path d="M3 7l9 4 9-4M12 11v10"/>',
        'route' => '<circle cx="6" cy="19" r="2"/><circle cx="18" cy="5" r="2"/><path d="M8 19h6a4 4 0 000-8h-4a4 4 0 010-8h6"/>',
        'tag' => '<path d="M3 12V3h9l9 9-9 9z"/><circle cx="7.5" cy="7.5" r="1.5"/>',
        'rupee' => '<path d="M6 4h12M6 9h12M6 4c7 0 7 10 0 10l8 7"/>',
        'history' => '<path d="M3 12a9 9 0 103-6.7L3 8"/><path d="M3 3v5h5M12 7v5l3 3"/>',
        'globe' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18M12 3c3 3 3 15 0 18M12 3c-3 3-3 15 0 18"/>',
        'settings' => '<circle cx="12" cy="12" r="3"/><path d="M12 2v3M12 19v3M2 12h3M19 12h3M4.9 4.9l2.1 2.1M17 17l2.1 2.1M4.9 19.1L7 17M17 7l2.1-2.1"/>',
        'inbox' => '<path d="M3 13l3-8h12l3 8v6H3z"/><path d="M3 13h5l1 3h6l1-3h5"/>',
        'user' => '<circle cx="12" cy="8" r="4"/><path d="M4 21a8 8 0 0116 0"/>',
        'phone' => '<path d="M5 3h4l2 5-3 2a11 11 0 006 6l2-3 5 2v4a2 2 0 01-2 2A18 18 0 013 5a2 2 0 012-2z"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 7l9 6 9-6"/>',
        'external' => '<path d="M14 4h6v6M20 4l-9 9M18 14v6H4V6h6"/>',
        'more' => '<circle cx="5" cy="12" r="1.5"/><circle cx="12" cy="12" r="1.5"/><circle cx="19" cy="12" r="1.5"/>',
        'archive' => '<rect x="3" y="4" width="18" height="4"/><path d="M5 8v12h14V8M10 12h4"/>',
        'pause' => '<path d="M8 5v14M16 5v14"/>',
        'restore' => '<path d="M3 12a9 9 0 109-9 9 9 0 00-6.4 2.6L3 8"/><path d="M3 3v5h5"/>',
        'calendar' => '<rect x="3" y="5" width="18" height="16" rx="2"/><path d="M3 10h18M8 3v4M16 3v4"/>',
        'info' => '<circle cx="12" cy="12" r="9"/><path d="M12 11v6M12 7.5v.5"/>',
    );
    return '<svg class="cms-i ' . e($cls) . '" viewBox="0 0 24 24" aria-hidden="true" focusable="false">' . (isset($p[$name]) ? $p[$name] : '') . '</svg>';
}

function status_pill($status)
{
    $label = isset(HG_STATUSES[$status]) ? HG_STATUSES[$status] : $status;
    return '<span class="cms-pill cms-pill--' . e($status) . '">' . e($label) . '</span>';
}

/** Package ID as shown inside the CMS. */
function pkg_id_badge(array $p, $long = false)
{
    list($id, $state) = pkg_id_state($p);
    if ($state === 'approved') return '<span class="cms-pid"><span class="cms-pid__k">Package ID</span> <b>' . e($id) . '</b></span>';
    if ($state === 'proposed') return '<span class="cms-pid cms-pid--proposed" title="Proposed in the migration mapping. Not assigned until the owner approves it; never shown publicly."><span class="cms-pid__k">Package ID</span> <b>' . e($id) . '</b> <small>' . ($long ? 'Proposed · pending owner approval' : 'Proposed') . '</small></span>';
    return '<span class="cms-pid cms-pid--pending"><span class="cms-pid__k">Package ID</span> <small>Pending approval</small></span>';
}

function field_text($name, $label, $value, array $o = array())
{
    $id = isset($o['id']) ? $o['id'] : 'f-' . preg_replace('/[^a-z0-9_]+/i', '-', $name);
    $attrs = '';
    foreach (array('maxlength', 'placeholder', 'min', 'max', 'step', 'pattern', 'inputmode', 'autocomplete') as $a) if (isset($o[$a])) $attrs .= ' ' . $a . '="' . e($o[$a]) . '"';
    if (!empty($o['required'])) $attrs .= ' required';
    if (!empty($o['readonly'])) $attrs .= ' readonly';
    if (!empty($o['disabled'])) $attrs .= ' disabled';
    if (!empty($o['counter'])) $attrs .= ' data-cms-count="' . e($o['counter']) . '"';
    $hint = isset($o['hint']) ? '<small class="cms-hint" id="' . $id . '-h">' . $o['hint'] . '</small>' : '';
    if ($hint) $attrs .= ' aria-describedby="' . $id . '-h"';
    $type = isset($o['type']) ? $o['type'] : 'text';
    $req = !empty($o['required']) ? ' <span class="cms-req" aria-hidden="true">*</span>' : '';
    if ($type === 'textarea') {
        $input = '<textarea id="' . $id . '" name="' . e($name) . '" rows="' . (isset($o['rows']) ? (int) $o['rows'] : 3) . '"' . $attrs . '>' . e($value) . '</textarea>';
    } else {
        $input = '<input id="' . $id . '" name="' . e($name) . '" type="' . e($type) . '" value="' . e($value) . '"' . $attrs . '>';
    }
    return '<div class="cms-field' . (isset($o['class']) ? ' ' . e($o['class']) : '') . '"><label for="' . $id . '">' . e($label) . $req . '</label>' . $input . $hint . '</div>';
}

function field_select($name, $label, $value, array $options, array $o = array())
{
    $id = isset($o['id']) ? $o['id'] : 'f-' . preg_replace('/[^a-z0-9_]+/i', '-', $name);
    $h = '';
    foreach ($options as $k => $v) {
        if (is_int($k)) $k = $v;
        $h .= '<option value="' . e($k) . '"' . ((string) $k === (string) $value ? ' selected' : '') . '>' . e($v === '' ? '— Select —' : $v) . '</option>';
    }
    $req = !empty($o['required']) ? ' <span class="cms-req" aria-hidden="true">*</span>' : '';
    $attrs = (!empty($o['required']) ? ' required' : '') . (!empty($o['disabled']) ? ' disabled' : '') . (isset($o['data']) ? ' ' . $o['data'] : '');
    $hint = isset($o['hint']) ? '<small class="cms-hint" id="' . $id . '-h">' . $o['hint'] . '</small>' : '';
    if ($hint) $attrs .= ' aria-describedby="' . $id . '-h"';
    return '<div class="cms-field' . (isset($o['class']) ? ' ' . e($o['class']) : '') . '"><label for="' . $id . '">' . e($label) . $req . '</label><select id="' . $id . '" name="' . e($name) . '"' . $attrs . '>' . $h . '</select>' . $hint . '</div>';
}

function field_check($name, $label, $checked, $value = '1', array $o = array())
{
    $id = isset($o['id']) ? $o['id'] : 'f-' . preg_replace('/[^a-z0-9_]+/i', '-', $name . '-' . $value);
    return '<label class="cms-check" for="' . $id . '"><input type="checkbox" id="' . $id . '" name="' . e($name) . '" value="' . e($value) . '"' . ($checked ? ' checked' : '') . (!empty($o['disabled']) ? ' disabled' : '') . '><span>' . e($label) . '</span></label>';
}

function card($title, $body, array $o = array())
{
    $id = isset($o['id']) ? ' id="' . e($o['id']) . '"' : '';
    $hid = 'c-' . substr(md5($title . (isset($o['id']) ? $o['id'] : '')), 0, 8);
    $sub = isset($o['sub']) ? '<p class="cms-card__sub">' . $o['sub'] . '</p>' : '';
    $act = isset($o['actions']) ? '<div class="cms-card__actions">' . $o['actions'] . '</div>' : '';
    return '<section class="cms-card' . (isset($o['class']) ? ' ' . e($o['class']) : '') . '"' . $id . ' aria-labelledby="' . $hid . '"><header class="cms-card__head"><div><h2 class="cms-card__title" id="' . $hid . '">' . e($title) . '</h2>' . $sub . '</div>' . $act . '</header>' . $body . '</section>';
}

function page_head($title, $sub = '', $right = '', $meta = '')
{
    return '<div class="cms-pagehead"><div class="cms-pagehead__text"><h1>' . e($title) . '</h1>' . ($sub ? '<p>' . e($sub) . '</p>' : '') . ($meta ? '<div class="cms-pagehead__meta">' . $meta . '</div>' : '') . '</div>' . ($right ? '<div class="cms-pagehead__actions">' . $right . '</div>' : '') . '</div>';
}

function empty_state($title, $text, $action = '')
{
    return '<div class="cms-empty">' . icon('box') . '<p class="cms-empty__t">' . e($title) . '</p><p>' . $text . '</p>' . $action . '</div>';
}
