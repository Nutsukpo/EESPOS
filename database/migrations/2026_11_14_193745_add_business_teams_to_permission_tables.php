<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if ($this->shouldSkipForSqlite()) {
            return;
        }

        /*
         * The roles table may already have been updated
         * by the earlier partially completed migration.
         */
        if (!Schema::hasColumn('roles', 'business_id')) {
            Schema::table('roles', function (Blueprint $table) {
                $table->unsignedBigInteger('business_id')->nullable();

                $table->dropUnique('roles_name_guard_name_unique');

                $table->unique(
                    ['business_id', 'name', 'guard_name'],
                    'roles_business_id_name_guard_name_unique'
                );
            });
        }

        /*
         * Update model_has_roles.
         */

        // Remove the foreign key before changing the primary key.
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign('model_has_roles_role_id_foreign');
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropPrimary();
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->primary(
                ['business_id', 'role_id', 'model_id', 'model_type'],
                'model_has_roles_business_role_model_type_primary'
            );

            $table->index(
                'business_id',
                'model_has_roles_business_id_index'
            );

            $table->index(
                'role_id',
                'model_has_roles_role_id_index'
            );
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->foreign(
                'role_id',
                'model_has_roles_role_id_foreign'
            )->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });

        /*
         * Update model_has_permissions.
         */

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropForeign(
                'model_has_permissions_permission_id_foreign'
            );
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->unsignedBigInteger('business_id');

            $table->dropPrimary();
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->primary(
                ['business_id', 'permission_id', 'model_id', 'model_type'],
                'model_has_permissions_business_permission_model_type_primary'
            );

            $table->index(
                'business_id',
                'model_has_permissions_business_id_index'
            );

            $table->index(
                'permission_id',
                'model_has_permissions_permission_id_index'
            );
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->foreign(
                'permission_id',
                'model_has_permissions_permission_id_foreign'
            )->references('id')
                ->on('permissions')
                ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        if ($this->shouldSkipForSqlite()) {
            return;
        }

        /*
         * Restore model_has_permissions.
         */
        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->dropForeign(
                'model_has_permissions_permission_id_foreign'
            );

            $table->dropPrimary();

            $table->dropIndex(
                'model_has_permissions_business_id_index'
            );

            $table->dropIndex(
                'model_has_permissions_permission_id_index'
            );

            $table->dropColumn('business_id');

            $table->primary(
                ['permission_id', 'model_id', 'model_type']
            );
        });

        Schema::table('model_has_permissions', function (Blueprint $table) {
            $table->foreign('permission_id')
                ->references('id')
                ->on('permissions')
                ->onDelete('cascade');
        });

        /*
         * Restore model_has_roles.
         */
        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->dropForeign('model_has_roles_role_id_foreign');

            $table->dropPrimary();

            $table->dropIndex(
                'model_has_roles_business_id_index'
            );

            $table->dropIndex(
                'model_has_roles_role_id_index'
            );

            $table->dropColumn('business_id');

            $table->primary(
                ['role_id', 'model_id', 'model_type']
            );
        });

        Schema::table('model_has_roles', function (Blueprint $table) {
            $table->foreign('role_id')
                ->references('id')
                ->on('roles')
                ->onDelete('cascade');
        });

        /*
         * Restore the original roles structure.
         */
        Schema::table('roles', function (Blueprint $table) {
            $table->dropUnique(
                'roles_business_id_name_guard_name_unique'
            );

            $table->dropColumn('business_id');

            $table->unique(
                ['name', 'guard_name'],
                'roles_name_guard_name_unique'
            );
        });
    }

    private function shouldSkipForSqlite(): bool
    {
        return Schema::getConnection()->getDriverName() === 'sqlite'
            && Schema::hasColumn('roles', 'business_id')
            && Schema::hasColumn('model_has_roles', 'business_id')
            && Schema::hasColumn('model_has_permissions', 'business_id');
    }

};
