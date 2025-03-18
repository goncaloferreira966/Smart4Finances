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
            // Administrator, Client
            $table->enum('type', ['A', 'C'])->default('C');

            // Nickname - must be unique
            $table->string('nickname', 20)->unique();

            // User access is blocked
            $table->boolean('blocked')->default(false);

            // User Photo/Avatar
            $table->string('photo_filename')->nullable();

            //  Balance/value
            $table->decimal('value', 10, 2)->default(0);

            // Currency - Accepts USD, €, and other currencies
            $table->enum('coin', ['$', '€', '£', 'R$'])->default('$');

            // custom data
            $table->json('custom')->nullable();

            // Users can be deleted with "soft deletes"
            $table->softDeletes();
        });

        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        // Inserir categorias iniciais TESTE
        DB::table('categories')->insert([
            ['name' => 'Comida', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Água', 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Luz', 'created_at' => now(), 'updated_at' => now()],
        ]);

        Schema::create('incomes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('source');
            $table->decimal('amount', 10, 2);
            $table->date('date');
            $table->string('recurring_interval', 20)->nullable();
            $table->enum('recurring_interval_unit', ['dia', 'semanal', 'mes', 'anual'])->nullable();
            $table->string('receipt', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('expenses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->string('description');
            $table->date('date');
            $table->string('recurring_interval', 20)->nullable();
            $table->enum('recurring_interval_unit', ['dia', 'semanal', 'mes', 'anual'])->nullable();
            $table->string('receipt', 255)->nullable();
            $table->timestamps();
        });

        Schema::create('investments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('type');
            $table->decimal('amount', 10, 2);
            $table->decimal('roi', 5, 2)->nullable();
            $table->timestamps();
        });

        Schema::create('budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->decimal('limit_amount', 10, 2);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
