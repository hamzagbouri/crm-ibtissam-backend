<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('mouvement_stocks', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->date('date');
            $table->integer('quantity');
            $table->foreignId('produit_id')->nullable()->constrained('produit_finis')->onDelete('cascade');
            $table->foreignId('matierePremiere_id')->nullable()->constrained('matiere_premieres')->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mouvement_stocks');
    }
};
