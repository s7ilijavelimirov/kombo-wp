<?php

namespace WpTheme\Providers;

class UpdateFilters
{
    public static function DisableThemeUpdate($value)
    {
        if (isset($value) && is_object($value)) {
            unset($value->response['drift']);
        }
        return $value;
    }
}
