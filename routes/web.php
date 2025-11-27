<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

// --- CORRECCIÓN PARA FILAMENT ---
// Esto arregla el error "Route [login] not defined".
// Redirige a cualquiera que busque el login normal hacia el login de Filament.
Route::get('/login', function () {
    return redirect()->route('filament.admin.auth.login');
})->name('login');
