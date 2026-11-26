<?php
require_once __DIR__ . '/profile_fields.php';
require_once __DIR__ . '/profile_stats.php';

/** About me, tag groups (interests, languages), lock stats and badges. All output is escaped. */
function profileRenderExtras(array $row, array $stats): string
{
    $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $h = '';

    if (!empty($row['about_me'])) {
        $h .= '<div class="pv-about">' . nl2br($e($row['about_me'])) . '</div>';
    }

    foreach (profileFieldDefs() as $key => $def) {
        if ($def['type'] !== 'multiselect') continue;
        $labels = profileValueList($key, $row[$key] ?? null);
        if (!$labels) continue;
        $h .= '<div class="pv-tags-block"><span class="pv-tags-title">' . $e($def['label']) . '</span><div class="pv-tags">';
        foreach ($labels as $l) $h .= '<span class="pv-tag">' . $e($l) . '</span>';
        $h .= '</div></div>';
    }

    return $h;
}

function profileRenderChips(array $facts): string
{
    $e = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    if (!$facts) return '';
    $h = '<div class="pv-chips">';
    foreach ($facts as $f) {
        $h .= '<span class="pv-chip"><small>' . $e($f['label']) . '</small>' . $e($f['value']) . '</span>';
    }
    return $h . '</div>';
}