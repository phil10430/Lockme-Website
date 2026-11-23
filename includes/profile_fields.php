<?php
/**
 * Profile field schema.
 * To add a field: 1) add a DB column in user_details with the same name as the key,
 *                 2) add one entry below. Validation, saving and the form follow automatically.
 *
 * type 'text'   -> needs 'max'
 * type 'select' -> needs 'options' (key => label)
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
                'location' => [
                    'label' => 'Location', 'type' => 'text', 'max' => 100,
                    'placeholder' => 'Country or region is enough', 'full' => true,
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
                        'single'          => 'Single',
                        'partnered_closed' => 'Partnered closed',
                        'partnered_open'   => 'Partnered open',
                        'poly'            => 'Poly',
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
            ],
        ],
    ];
}

/** Flat list: key => definition */
function profileFieldDefs(): array
{
    $all = [];
    foreach (profileFieldGroups() as $group) {
        foreach ($group['fields'] as $key => $def) {
            $all[$key] = $def;
        }
    }
    return $all;
}

/** SQL column list for SELECT, e.g. "d.headline, d.location, ..." */
function profileFieldColumnsSql(string $alias = 'd'): string
{
    return implode(', ', array_map(fn($k) => "$alias.`$k`", array_keys(profileFieldDefs())));
}

/**
 * Validates input (usually $_POST['profile']).
 * Returns key => value|null. On error returns [] and sets $error.
 */
function profileCollectFields($input, ?string &$error = null): array
{
    $input  = is_array($input) ? $input : [];
    $values = [];

    foreach (profileFieldDefs() as $key => $def) {
        $raw = $input[$key] ?? '';
        $raw = is_string($raw) ? trim($raw) : '';
        $raw = preg_replace('/[\x00-\x1F\x7F]/u', '', $raw) ?? '';

        if ($raw === '') {
            $values[$key] = null;
            continue;
        }

        if ($def['type'] === 'select') {
            if (!array_key_exists($raw, $def['options'])) {
                $error = 'Invalid value for ' . $def['label'] . '.';
                return [];
            }
        } elseif (mb_strlen($raw) > (int)$def['max']) {
            $error = $def['label'] . ' must not exceed ' . $def['max'] . ' characters.';
            return [];
        }
        $values[$key] = $raw;
    }
    return $values;
}

/** Display label for a stored select value (for public profile views). */
function profileOptionLabel(string $key, ?string $value): string
{
    $def = profileFieldDefs()[$key] ?? null;
    if ($value === null || $value === '' || !$def) return '';
    return $def['type'] === 'select' ? ($def['options'][$value] ?? '') : $value;
}

function profileRenderField(string $key, array $def, ?string $value): string
{
    $id   = 'pf_' . $key;
    $name = 'profile[' . $key . ']';
    $e    = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
    $cls  = 'pf-field' . (!empty($def['full']) ? ' pf-full' : '');

    $html  = '<div class="' . $cls . '">';
    $html .= '<label for="' . $e($id) . '">' . $e($def['label']) . '</label>';

    if ($def['type'] === 'select') {
        $html .= '<select id="' . $e($id) . '" name="' . $e($name) . '">';
        $html .= '<option value="">– Not specified –</option>';
        foreach ($def['options'] as $optKey => $optLabel) {
            $sel = ((string)$value === (string)$optKey) ? ' selected' : '';
            $html .= '<option value="' . $e($optKey) . '"' . $sel . '>' . $e($optLabel) . '</option>';
        }
        $html .= '</select>';
    } else {
        $html .= '<input type="text" id="' . $e($id) . '" name="' . $e($name) . '"'
              .  ' maxlength="' . (int)$def['max'] . '"'
              .  ' placeholder="' . $e($def['placeholder'] ?? '') . '"'
              .  ' value="' . $e($value) . '">';
    }
    return $html . '</div>';
}

/** Renders all schema groups as fieldsets for the edit dialog. */
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