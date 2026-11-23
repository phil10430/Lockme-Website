<?php
require_once __DIR__ . '/profile_helpers.php';

/**
 * Profile field schema.
 * To add a field: 1) add a DB column in user_details named like the key,
 *                 2) add one entry below.
 *
 * type 'text'        -> 'max'
 * type 'textarea'    -> 'max', optional 'rows'
 * type 'select'      -> 'options' (key => label)
 * type 'multiselect' -> 'options', optional 'max_items'
 * optional for all: 'hint', 'placeholder', 'full'
 */
function profileFieldGroups(): array
{
    return [
        'about' => [
            'title'  => 'About you',
            'fields' => [
                'headline' => [
                    'label' => 'Headline', 'type' => 'text', 'max' => 50,
                    'placeholder' => 'One line about you', 'full' => true,
                ],
                'pronouns' => [
                    'label' => 'Pronouns', 'type' => 'select',
                    'options' => [
                        'he_him'   => 'he/him',
                        'she_her'  => 'she/her',
                        'they_them'=> 'they/them',
                        'other'    => 'Other',
                    ],
                ],
                'location' => [
                    'label' => 'Location', 'type' => 'text', 'max' => 100,
                    'placeholder' => 'Country or region is enough',
                ],
                'about_me' => [
                    'label' => 'About me', 'type' => 'textarea', 'max' => 1000, 'rows' => 6,
                    'placeholder' => 'Tell the community about yourself …',
                    'hint' => 'Visible on your public profile. Please do not post contact details here.',
                ],
            ],
        ],
        'preferences' => [
            'title'  => 'Role & preferences',
            'fields' => [
                'profile_role' => [
                    'label' => 'Role', 'type' => 'select',
                    'options' => [
                        'lockee'    => 'Lockee',
                        'keyholder' => 'Keyholder',
                        'switch'    => 'Switch',
                        'searching' => 'Still Searching',
                    ],
                ],
                'looking_for' => [
                    'label' => 'Looking for', 'type' => 'select',
                    'options' => [
                        'lockee'          => 'Lockee',
                        'keyholder'       => 'Keyholder',
                        'nothing_special' => 'Nothing special',
                    ],
                ],
                'relationship_status' => [
                    'label' => 'Relationship', 'type' => 'select',
                    'options' => [
                        'single'           => 'Single',
                        'partnered_closed' => 'Partnered closed',
                        'partnered_open'   => 'Partnered open',
                        'poly'             => 'Poly',
                    ],
                ],
                'remote_keyholding' => [
                    'label' => 'Remote Keyholding', 'type' => 'select',
                    'options' => [
                        'yes'        => 'Yes',
                        'interested' => 'Interested',
                        'no'         => 'No',
                    ],
                ],
                'contact_pref' => [
                    'label' => 'Contact', 'type' => 'select',
                    'options' => [
                        'open'   => 'Open to messages',
                        'social' => 'Via social links only',
                        'none'   => 'Not looking for contact',
                    ],
                ],
            ],
        ],
        'experience' => [
            'title'  => 'Experience & interests',
            'fields' => [
                'experience' => [
                    'label' => 'Experience', 'type' => 'select',
                    'options' => [
                        'beginner'     => 'Beginner',
                        'intermediate' => 'Intermediate',
                        'experienced'  => 'Experienced',
                        'longterm'     => 'Long-term',
                    ],
                ],
                'interests' => [
                    'label' => 'Interests', 'type' => 'multiselect', 'max_items' => 10,
                    'options' => [
                        'denial'          => 'Denial',
                        'orgasm_control'  => 'Orgasm Control',
                        'long_term'       => 'Long-term Lock',
                        'tasks'           => 'Tasks',
                        'keyholder_games' => 'Keyholder Games',
                        'humiliation'     => 'Humiliation',
                        'service'         => 'Service',
                        'romance'         => 'Romance',
                        'online_only'     => 'Online only',
                    ],
                ],
                'languages' => [
                    'label' => 'Languages', 'type' => 'multiselect', 'max_items' => 5,
                    'options' => [
                        'en' => 'English',    'de' => 'German',  'fr' => 'French',
                        'es' => 'Spanish',    'it' => 'Italian', 'pt' => 'Portuguese',
                        'nl' => 'Dutch',      'pl' => 'Polish',  'ru' => 'Russian',
                        'tr' => 'Turkish',    'other' => 'Other',
                    ],
                ],
            ],
        ],
    ];
}

/** Flat list: key => definition */
function profileFieldDefs(): array
{
    $all = [];
    foreach (profileFieldGroups() as $group) {
        foreach ($group['fields'] as $key => $def) $all[$key] = $def;
    }
    return $all;
}

function profileFieldColumnsSql(string $alias = 'd'): string
{
    return implode(', ', array_map(fn($k) => "$alias.`$k`", array_keys(profileFieldDefs())));
}

/**
 * Validates input (usually $_POST['profile']).
 * Returns key => string|null. On error returns [] and sets $error.
 */
function profileCollectFields($input, ?string &$error = null): array
{
    $input  = is_array($input) ? $input : [];
    $values = [];

    foreach (profileFieldDefs() as $key => $def) {
        $raw = $input[$key] ?? null;

        if ($def['type'] === 'multiselect') {
            $picked = [];
            foreach ((is_array($raw) ? $raw : []) as $item) {
                if (!is_string($item) || !array_key_exists($item, $def['options'])) {
                    $error = 'Invalid value for ' . $def['label'] . '.';
                    return [];
                }
                $picked[$item] = true;
            }
            // keep the order of the schema
            $ordered = array_values(array_filter(array_keys($def['options']), fn($k) => isset($picked[$k])));
            if (count($ordered) > (int)($def['max_items'] ?? 10)) {
                $error = 'Too many selections for ' . $def['label'] . '.';
                return [];
            }
            $values[$key] = $ordered ? implode(',', $ordered) : null;
            continue;
        }

        $str = is_string($raw) ? $raw : '';

        if ($def['type'] === 'textarea') {
            $str = str_replace(["\r\n", "\r"], "\n", $str);
            $str = preg_replace('/[^\P{C}\n]/u', '', $str) ?? '';   // control chars except newline
            $str = trim(preg_replace("/\n{3,}/", "\n\n", $str) ?? '');
        } else {
            $str = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $str) ?? '');
        }

        if ($str === '') { $values[$key] = null; continue; }

        if ($def['type'] === 'select') {
            if (!array_key_exists($str, $def['options'])) {
                $error = 'Invalid value for ' . $def['label'] . '.';
                return [];
            }
        } elseif (mb_strlen($str) > (int)$def['max']) {
            $error = $def['label'] . ' must not exceed ' . $def['max'] . ' characters.';
            return [];
        }
        $values[$key] = $str;
    }
    return $values;
}

/** Labels of a stored multiselect value. */
function profileValueList(string $key, ?string $value): array
{
    $def = profileFieldDefs()[$key] ?? null;
    if (!$def || $def['type'] !== 'multiselect' || $value === null || $value === '') return [];
    $out = [];
    foreach (explode(',', $value) as $k) {
        if (isset($def['options'][$k])) $out[] = $def['options'][$k];
    }
    return $out;
}

/** Display label for a stored select/multiselect value. */
function profileOptionLabel(string $key, ?string $value): string
{
    $def = profileFieldDefs()[$key] ?? null;
    if ($value === null || $value === '' || !$def) return '';
    if ($def['type'] === 'multiselect') return implode(', ', profileValueList($key, $value));
    return $def['type'] === 'select' ? ($def['options'][$value] ?? '') : $value;
}

/** Chips for public views: age, location and all single selects. */
function profileBuildFacts(array $row): array
{
    $facts = [];
    $age = profileAge($row['birth_date'] ?? null);
    if ($age !== null) $facts[] = ['label' => 'Age', 'value' => (string)$age];
    if (!empty($row['location'])) $facts[] = ['label' => 'Location', 'value' => (string)$row['location']];
    foreach (profileFieldDefs() as $key => $def) {
        if ($def['type'] !== 'select') continue;
        $label = profileOptionLabel($key, $row[$key] ?? null);
        if ($label !== '') $facts[] = ['label' => $def['label'], 'value' => $label];
    }
    return $facts;
}

function profileRenderField(string $key, array $def, ?string $value): string
{
    $id   = 'pf_' . $key;
    $name = 'profile[' . $key . ']';
    $e    = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $full = !empty($def['full']) || in_array($def['type'], ['textarea', 'multiselect'], true);

    $html = '<div class="pf-field' . ($full ? ' pf-full' : '') . '">';

    if ($def['type'] === 'multiselect') {
        $selected = ($value !== null && $value !== '') ? explode(',', $value) : [];
        $html .= '<span class="pf-label">' . $e($def['label']) . '</span><div class="pf-tags">';
        foreach ($def['options'] as $optKey => $optLabel) {
            $chk = in_array((string)$optKey, $selected, true) ? ' checked' : '';
            $html .= '<label class="pf-tag"><input type="checkbox" name="' . $e($name) . '[]" value="' . $e($optKey) . '"' . $chk . '>'
                  .  '<span>' . $e($optLabel) . '</span></label>';
        }
        $html .= '</div>';
    } else {
        $html .= '<label for="' . $e($id) . '">' . $e($def['label']) . '</label>';
        if ($def['type'] === 'select') {
            $html .= '<select id="' . $e($id) . '" name="' . $e($name) . '"><option value="">– Not specified –</option>';
            foreach ($def['options'] as $optKey => $optLabel) {
                $sel = ((string)$value === (string)$optKey) ? ' selected' : '';
                $html .= '<option value="' . $e($optKey) . '"' . $sel . '>' . $e($optLabel) . '</option>';
            }
            $html .= '</select>';
        } elseif ($def['type'] === 'textarea') {
            $html .= '<textarea id="' . $e($id) . '" name="' . $e($name) . '" rows="' . (int)($def['rows'] ?? 5) . '"'
                  .  ' maxlength="' . (int)$def['max'] . '" placeholder="' . $e($def['placeholder'] ?? '') . '">'
                  .  $e($value) . '</textarea>';
        } else {
            $html .= '<input type="text" id="' . $e($id) . '" name="' . $e($name) . '"'
                  .  ' maxlength="' . (int)$def['max'] . '" placeholder="' . $e($def['placeholder'] ?? '') . '"'
                  .  ' value="' . $e($value) . '">';
        }
    }

    if (!empty($def['hint'])) $html .= '<small class="pf-hint">' . $e($def['hint']) . '</small>';
    return $html . '</div>';
}

function profileRenderFormSections(array $user): string
{
    $html = '';
    foreach (profileFieldGroups() as $group) {
        $html .= '<fieldset class="pf-section"><legend>' . htmlspecialchars($group['title']) . '</legend><div class="pf-grid">';
        foreach ($group['fields'] as $key => $def) {
            $html .= profileRenderField($key, $def, $user[$key] ?? null);
        }
        $html .= '</div></fieldset>';
    }
    return $html;
}