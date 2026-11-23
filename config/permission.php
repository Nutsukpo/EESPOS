<?php

use Spatie\Permission\DefaultTeamResolver;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

return [

    /*
     * Models used by Spatie Permission.
     */
    'models' => [

        'permission' => Permission::class,

        'role' => Role::class,

        'team' => null,

        'default_model' => null,
    ],

    /*
     * Database table names.
     */
    'table_names' => [

        'roles' => 'roles',

        'permissions' => 'permissions',

        'model_has_permissions' => 'model_has_permissions',

        'model_has_roles' => 'model_has_roles',

        'role_has_permissions' => 'role_has_permissions',
    ],

    /*
     * Database column names.
     */
    'column_names' => [

        'role_pivot_key' => null,

        'permission_pivot_key' => null,

        'model_morph_key' => 'model_id',

        'team_foreign_key' => 'business_id',
    ],

    /*
     * Permission checking.
     */
    'register_permission_check_method' => true,

    'register_octane_reset_listener' => false,

    'events_enabled' => false,

    /*
     * Enable business-specific roles and permissions.
     */
    'teams' => true,

    'team_resolver' => DefaultTeamResolver::class,

    'use_passport_client_credentials' => false,

    'display_permission_in_exception' => false,

    'display_role_in_exception' => false,

    'enable_wildcard_permission' => false,

    // 'wildcard_permission' => Spatie\Permission\WildcardPermission::class,

    /*
     * Permission cache settings.
     */
    'cache' => [

        'expiration_time' => DateInterval::createFromDateString('24 hours'),

        'key' => 'spatie.permission.cache',

        'store' => 'default',
    ],

];
