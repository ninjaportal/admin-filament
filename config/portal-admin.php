<?php

return [
    'panel' => [
        'id' => 'portal-admin',
        'path' => 'admin',
        'domain' => null,
        'default' => true,
        'guard' => 'admin_panel',
        'provider' => 'admins',
        'password_broker' => 'admins',
        'rbac_guard' => (string) config('ninjaportal.auth.guards.admin', 'admin'),
        'brand_name' => 'NinjaPortal',
        'brand_logo' => null,
        'brand_logo_height' => '2rem',
        'profile' => true,
        'login' => true,
        'password_reset' => false,
        'navigation' => [
            'users' => 'Users',
            'catalog' => 'Catalog',
            'access' => 'Access',
            'system' => 'System',
        ],
        'colors' => [
            'primary' => 'amber',
        ],
        'icons' => [
            'dashboard' => 'heroicon-o-home-modern',
            'admins' => 'heroicon-o-shield-check',
            'users' => 'heroicon-o-user-group',
            'audiences' => 'heroicon-o-users',
            'api_products' => 'heroicon-o-cube',
            'categories' => 'heroicon-o-folder',
            'roles' => 'heroicon-o-identification',
            'permissions' => 'heroicon-o-key',
            'settings' => 'heroicon-o-cog-6-tooth',
            'setting_groups' => 'heroicon-o-adjustments-horizontal',
        ],
    ],

    'dashboard' => [
        'stats' => [
            'cache_ttl' => 300,
            'app_count_uses_synced_users_only' => true,
        ],
    ],

    'resources' => [
        'admins' => [
            'resource' => \NinjaPortal\Admin\Resources\Admins\AdminResource::class,
            'pages' => [
                'index' => \NinjaPortal\Admin\Resources\Admins\Pages\ListAdmins::class,
                'create' => \NinjaPortal\Admin\Resources\Admins\Pages\CreateAdmin::class,
                'edit' => \NinjaPortal\Admin\Resources\Admins\Pages\EditAdmin::class,
            ],
        ],
        'users' => [
            'resource' => \NinjaPortal\Admin\Resources\Users\UserResource::class,
            'pages' => [
                'index' => \NinjaPortal\Admin\Resources\Users\Pages\ListUsers::class,
                'create' => \NinjaPortal\Admin\Resources\Users\Pages\CreateUser::class,
                'edit' => \NinjaPortal\Admin\Resources\Users\Pages\EditUser::class,
                'apps' => \NinjaPortal\Admin\Resources\Users\Pages\ManageUserApps::class,
            ],
        ],
        'audiences' => [
            'resource' => \NinjaPortal\Admin\Resources\Audiences\AudienceResource::class,
            'pages' => [
                'index' => \NinjaPortal\Admin\Resources\Audiences\Pages\ListAudiences::class,
                'create' => \NinjaPortal\Admin\Resources\Audiences\Pages\CreateAudience::class,
                'edit' => \NinjaPortal\Admin\Resources\Audiences\Pages\EditAudience::class,
            ],
        ],
        'api_products' => [
            'resource' => \NinjaPortal\Admin\Resources\ApiProducts\ApiProductResource::class,
            'pages' => [
                'index' => \NinjaPortal\Admin\Resources\ApiProducts\Pages\ListApiProducts::class,
                'create' => \NinjaPortal\Admin\Resources\ApiProducts\Pages\CreateApiProduct::class,
                'edit' => \NinjaPortal\Admin\Resources\ApiProducts\Pages\EditApiProduct::class,
            ],
        ],
        'categories' => [
            'resource' => \NinjaPortal\Admin\Resources\Categories\CategoryResource::class,
            'pages' => [
                'index' => \NinjaPortal\Admin\Resources\Categories\Pages\ListCategories::class,
                'create' => \NinjaPortal\Admin\Resources\Categories\Pages\CreateCategory::class,
                'edit' => \NinjaPortal\Admin\Resources\Categories\Pages\EditCategory::class,
            ],
        ],
        'roles' => [
            'resource' => \NinjaPortal\Admin\Resources\Roles\RoleResource::class,
            'pages' => [
                'index' => \NinjaPortal\Admin\Resources\Roles\Pages\ListRoles::class,
                'create' => \NinjaPortal\Admin\Resources\Roles\Pages\CreateRole::class,
                'edit' => \NinjaPortal\Admin\Resources\Roles\Pages\EditRole::class,
            ],
        ],
        'permissions' => [
            'resource' => \NinjaPortal\Admin\Resources\Permissions\PermissionResource::class,
            'pages' => [
                'index' => \NinjaPortal\Admin\Resources\Permissions\Pages\ListPermissions::class,
                'create' => \NinjaPortal\Admin\Resources\Permissions\Pages\CreatePermission::class,
                'edit' => \NinjaPortal\Admin\Resources\Permissions\Pages\EditPermission::class,
            ],
        ],
        'settings' => [
            'resource' => \NinjaPortal\Admin\Resources\Settings\SettingResource::class,
            'pages' => [
                'index' => \NinjaPortal\Admin\Resources\Settings\Pages\ListSettings::class,
                'create' => \NinjaPortal\Admin\Resources\Settings\Pages\CreateSetting::class,
                'edit' => \NinjaPortal\Admin\Resources\Settings\Pages\EditSetting::class,
            ],
        ],
        'setting_groups' => [
            'resource' => \NinjaPortal\Admin\Resources\SettingGroups\SettingGroupResource::class,
            'pages' => [
                'index' => \NinjaPortal\Admin\Resources\SettingGroups\Pages\ListSettingGroups::class,
                'create' => \NinjaPortal\Admin\Resources\SettingGroups\Pages\CreateSettingGroup::class,
                'edit' => \NinjaPortal\Admin\Resources\SettingGroups\Pages\EditSettingGroup::class,
            ],
        ],
    ],

    'pages' => [
        'dashboard' => \NinjaPortal\Admin\Pages\Dashboard::class,
    ],

    'widgets' => [
        \NinjaPortal\Admin\Widgets\PortalOverviewStatsWidget::class,
    ],
];
