<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Añadir los campos requeridos
            $table->string('surname', 255);
            $table->string('company', 255)->nullable();
            $table->string('nif', 255)->nullable();
            $table->string('address', 255)->nullable();
            $table->string('city', 255)->nullable();
            $table->string('zip_code', 255)->nullable();
            $table->string('phone', 255)->nullable();
            $table->unsignedBigInteger('prefix_id')->nullable();
            $table->boolean('terms_conditions');
            $table->boolean('privacy_policy');
            $table->unsignedBigInteger('country_id')->nullable();

            // Definir claves foráneas
            $table->foreign('prefix_id')->references('id')->on('countries')->onDelete('set null');
            $table->foreign('country_id')->references('id')->on('countries')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Eliminar las columnas en caso de revertir la migración
            $table->dropForeign(['prefix_id']);
            $table->dropForeign(['role_id']);
            $table->dropForeign(['country_id']);
            $table->dropColumn([
                'surname', 'company', 'nif', 'address', 'city', 
                'zip_code', 'phone', 'prefix_id', 'role_id', 
                'terms_conditions', 'privacy_policy', 'country_id'
            ]);
        });
    }
};
