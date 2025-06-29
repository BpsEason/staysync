<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $teams = config('permission.teams');
        $tableNames = config('permission.table_names');
        $columnNames = config('permission.column_names');
        $pivotRole = $tableNames['role_has_permissions'];
        $pivotPermission = $tableNames['model_has_permissions'];
        $pivotModel = $tableNames['model_has_roles'];

        Schema::create($tableNames['permissions'], function (Blueprint $table) use ($columnNames, $teams) {
            $table->bigIncrements('id'); // permission id
            $table->string('name');       // For example 'edit articles'
            $table->string('guard_name'); // For example 'web'
            $table->unsignedBigInteger('tenant_id')->nullable()->index(); // Added for multi-tenancy
            
            $table->timestamps();

            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key']);
            }

            $table->unique(['name', 'guard_name', 'tenant_id']); // Unique per tenant
        });

        Schema::create($tableNames['roles'], function (Blueprint $table) use ($columnNames, $teams) {
            $table->bigIncrements('id'); // role id
            $table->string('name');       // For example 'admin' or 'super-admin'
            $table->string('guard_name'); // For example 'web'
            $table->unsignedBigInteger('tenant_id')->nullable()->index(); // Added for multi-tenancy
            
            $table->timestamps();
            
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key']);
            }
            $table->unique(['name', 'guard_name', 'tenant_id']); // Unique per tenant
        });

        Schema::create($pivotPermission, function (Blueprint $table) use ($tableNames, $columnNames, $pivotPermission, $teams) {
            $table->unsignedBigInteger(config('permission.column_names.model_morph_key'));
            $table->string('model_type');
            $table->foreignId('permission_id')
                ->constrained($tableNames['permissions'])
                ->cascadeOnDelete();
            
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key']);
            }
            
            $table->index([config('permission.column_names.model_morph_key'), 'model_type'], "model_has_permissions_model_id_model_type_index");
            $table->primary([
                'permission_id',
                config('permission.column_names.model_morph_key'),
                'model_type',
                $columnNames['team_foreign_key'] ?? null,
            ], 'model_has_permissions_permission_model_type_primary');
        });

        Schema::create($pivotModel, function (Blueprint $table) use ($tableNames, $columnNames, $pivotModel, $teams) {
            $table->unsignedBigInteger(config('permission.column_names.model_morph_key'));
            $table->string('model_type');
            $table->foreignId('role_id')
                ->constrained($tableNames['roles'])
                ->cascadeOnDelete();
            
            if ($teams) {
                $table->unsignedBigInteger($columnNames['team_foreign_key'])->nullable();
                $table->index($columnNames['team_foreign_key']);
            }
            
            $table->index([config('permission.column_names.model_morph_key'), 'model_type'], "model_has_roles_model_id_model_type_index");
            $table->primary([
                'role_id',
                config('permission.column_names.model_morph_key'),
                'model_type',
                $columnNames['team_foreign_key'] ?? null,
            ], 'model_has_roles_role_model_type_primary');
        });

        Schema::create($tableNames['role_has_permissions'], function (Blueprint $table) use ($tableNames) {
            $table->foreignId('permission_id')
                ->constrained($tableNames['permissions'])
                ->cascadeOnDelete();

            $table->foreignId('role_id')
                ->constrained($tableNames['roles'])
                ->cascadeOnDelete();

            $table->primary(['permission_id', 'role_id'], 'role_has_permissions_permission_id_role_id_primary');
        });

        app('cache')
            ->store(config('permission.cache.store') != 'default' ? config('permission.cache.store') : null)
            ->forget(config('permission.cache.key'));
    }

    public function down(): void
    {
        $tableNames = config('permission.table_names');

        Schema::drop($tableNames['role_has_permissions']);
        Schema::drop($tableNames['model_has_roles']);
        Schema::drop($tableNames['model_has_permissions']);
        Schema::drop($tableNames['roles']);
        Schema::drop($tableNames['permissions']);
    }
};
