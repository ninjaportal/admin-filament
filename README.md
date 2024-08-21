# Included Inside 
- Admin model
- ACL 
- API Catalog Management 
- User Management
- Settings Module


# Challenges 
[ ] Translation Switches 
[x] Use NinjaPortal Services to handle CRUD operations 
    [ ] Update Delete Action awe are still relying on the default Filament Delete Action (@egyjs)


If you want to use NinjaPortal Services with Filament Resources
1. use HasNinjaServiceTrait and implement the required methods
2. in the CreateRecord page switch the form to use CreateRecordWithService instead of CreateRecord
3. in the EditRecord page switch the form to use EditRecordWithService instead of EditRecord


## Settings 
| Column Name | Description                                                                                          |
| --- |------------------------------------------------------------------------------------------------------|
| key (unique) | The key of the setting, could be a config key path for overwriting the defined value in the config file |
| value | The value of the setting, could be a string, number, boolean                                |
| name | The name of the setting, could be a string, number, boolean                                |
| description | The description of the setting, could be a string, number, boolean                                |
| type | The type of the setting, could be a string, number, boolean                                |

## Setting Settings Values 
In the SettingsServiceProvider in the boot method.
```php

class SettingsServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $settings = Setting::all();
        foreach ($settings as $setting) {
            Config::set($setting->key, $setting->value);
        }
    }
}
```

