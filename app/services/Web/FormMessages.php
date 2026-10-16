<?php
declare(strict_types=1);

namespace App\Services\Web;

/**
 * Builds the validation-message map for Controller::validate() from EDITABLE copy (registry fields on the page that owns the
 * form), so error wording can be changed in the admin. Expected registry fields (section "form" by default):
 *   err_required  err_email  err_phone  err_max  err_url
 *
 *   $this->validate($rules, FormMessages::for('contact', array_keys($rules), $rules), $labels)
 */
final class FormMessages
{
    /**
     * @param string[]             $fields
     * @param array<string,string> $rules  the rules array (to know which messages apply to which field)
     * @return array<string,string>
     */
    public static function for(string $page, array $fields, array $rules, string $section = 'form'): array
    {
        $copy = static fn(string $key): string => trim((string) content($page, $section . '.' . $key, ''));
        $map = [
            'required' => $copy('err_required'),
            'email'    => $copy('err_email'),
            'phone'    => $copy('err_phone'),
            'max'      => $copy('err_max'),
            'url'      => $copy('err_url'),
        ];
        $out = [];
        foreach ($fields as $field) {
            $spec = $rules[$field] ?? '';
            $list = is_array($spec) ? $spec : explode('|', (string) $spec);
            foreach ($list as $rule) {
                $name = explode(':', (string) $rule, 2)[0];
                if (isset($map[$name]) && $map[$name] !== '') {
                    $out[$field . '.' . $name] = $map[$name];
                }
            }
        }
        return $out;
    }
}
