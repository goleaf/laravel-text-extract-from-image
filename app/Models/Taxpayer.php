<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Taxpayer extends Model
{
    protected $table = 'taxpayers';

    public $incrementing = false;

    protected $primaryKey = 'id';

    protected $keyType = 'string';

    public $timestamps = false;

    protected $fillable = [
        'id',
        'ja_kodas',
        'pavadinimas',
        'ireg_data',
        'isreg_data',
        'anul_data',
        'valstybe',
        'tipo_aprasymas',
        'tipas_nuo',
        'tipas_iki',
        'pvm_kodas_pref',
        'pvm_kodas',
        'pvm_iregistruota',
        'pvm_isregistruota',
        'padalinio_nr',
        'padalinio_pvd',
        'padalinio_savivaldybe',
        'padalinio_kodas',
        'suformuota',
        'isformuota',
        'veiklos_pradzia',
        'veiklos_pabaiga',
        'veikla_anuliuota',
        'pagrindine',
        'vv_savivaldybe',
        'vv_adresas_nuo',
        'vv_adresas_iki',
        'vv_adresas_anul',
        'mm_grupe',
        'grup_aprasymas',
        'grupe_nuo',
        'grupe_iki',
        'grupe_anul',
    ];

    // Cast fields to appropriate types
    protected $casts = [
        'ireg_data' => 'date',
        'isreg_data' => 'date',
        'anul_data' => 'date',
        'pvm_iregistruota' => 'date',
        'pvm_isregistruota' => 'date',
        'suformuota' => 'date',
        'isformuota' => 'date',
        'veiklos_pradzia' => 'date',
        'veiklos_pabaiga' => 'date',
        'veikla_anuliuota' => 'date',
        'vv_adresas_nuo' => 'date',
        'vv_adresas_iki' => 'date',
        'vv_adresas_anul' => 'date',
        'grupe_nuo' => 'date',
        'grupe_iki' => 'date',
        'grupe_anul' => 'date',
        'padalinio_nr' => 'integer',
        'padalinio_savivaldybe' => 'integer',
        'vv_savivaldybe' => 'integer',
        'pagrindine' => 'boolean',
    ];

}
