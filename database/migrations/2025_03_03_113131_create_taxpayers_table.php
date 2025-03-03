<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('taxpayers', function (Blueprint $table) {
            $table->string('id', 15)->index(); // e.g., "000008374518306", unique identifier
            $table->string('ja_kodas', 9)->nullable(); // e.g., "147629339", legal entity code
            $table->string('pavadinimas')->nullable(); // e.g., "P. Morkvėno firma \"Aistina\""
            $table->date('ireg_data')->nullable(); // e.g., "1996-10-21"
            $table->date('isreg_data')->nullable(); // e.g., "2024-08-29"
            $table->date('anul_data')->nullable(); // e.g., empty in sample, assuming nullable
            $table->string('valstybe', 3)->nullable(); // e.g., "LTU", country code
            $table->string('tipo_aprasymas')->nullable(); // e.g., "Individualios įmonės ir ūkinės, mažosios bendrijos"
            $table->string('pvm_kodas_pref', 2)->nullable(); // e.g., "LT", VAT prefix
            $table->string('pvm_kodas', 12)->nullable(); // e.g., "476293314"
            $table->date('pvm_iregistruota')->nullable(); // e.g., "1995-02-01"
            $table->date('pvm_isregistruota')->nullable(); // e.g., "2020-02-07"
            $table->unsignedTinyInteger('padalinio_nr')->nullable(); // e.g., 1 or 2, small integer
            $table->string('padalinio_pvd')->nullable(); // e.g., "siuvykla" or "Biuras (kontora)"
            $table->unsignedTinyInteger('padalinio_savivaldybe')->nullable(); // e.g., 27 or 19, small integer
            $table->string('padalinio_kodas')->nullable(); // e.g., "440" or empty
            $table->date('suformuota')->nullable(); // e.g., "2005-08-08"
            $table->date('isformuota')->nullable(); // e.g., "2024-08-29"
            $table->date('veiklos_pradzia')->nullable(); // e.g., "2015-09-17"
            $table->date('veiklos_pabaiga')->nullable(); // e.g., "2024-12-31"
            $table->date('veikla_anuliuota')->nullable(); // e.g., empty or "2022-08-10"
            $table->boolean('pagrindine')->nullable(); // e.g., 1 or 0
            $table->unsignedTinyInteger('vv_savivaldybe')->nullable(); // e.g., 27 or 19
            $table->date('vv_adresas_nuo')->nullable(); // e.g., "2015-09-17"
            $table->date('vv_adresas_iki')->nullable(); // e.g., "2024-08-29"
            $table->date('vv_adresas_anul')->nullable(); // e.g., empty
            $table->string('mm_grupe')->nullable(); // e.g., empty or could be a code
            $table->string('grup_aprasymas')->nullable(); // e.g., empty or description
            $table->date('grupe_nuo')->nullable(); // e.g., empty
            $table->date('grupe_iki')->nullable(); // e.g., empty
            $table->date('grupe_anul')->nullable(); // e.g., empty

            $table->timestamps(); // Created_at and updated_at columns
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('taxpayers');
    }
};
