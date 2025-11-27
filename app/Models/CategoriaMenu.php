<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CategoriaMenu extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'categoria_menu';

    protected $fillable = [
        
        'nombre',
        'visible_publico',
        'activo',
    
    ];

    protected $casts = [
        'visible_publico' => 'boolean',
        'activo' => 'boolean',
    ];


    /*
    |--------------------------------------------------------------------------
    | RELACIONES AUTOMÁTICAS
    |--------------------------------------------------------------------------
    */
    
}
