<?php

use App\Models\TreatedSalary;
use App\Services\PdfGenerateService;
use App\Settings\AppSettings;
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

Route::get('/salary-slip/{treatedSalary}', function (TreatedSalary $treatedSalary) {
    return (new PdfGenerateService(new AppSettings))->generateSalarySlip($treatedSalary);
})->name('salary-slip.download');
