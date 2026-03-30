<?php

use Illuminate\Support\Facades\Route;
use Lwekuiper\StatamicAcumbamail\Http\Controllers\AddonConfigController;
use Lwekuiper\StatamicAcumbamail\Http\Controllers\FormConfigController;
use Lwekuiper\StatamicAcumbamail\Http\Controllers\GetFormFieldsController;
use Lwekuiper\StatamicAcumbamail\Http\Controllers\GetMergeFieldsController;

Route::name('acumbamail.')->prefix('acumbamail')->group(function () {
    Route::get('/', [FormConfigController::class, 'index'])->name('index');

    Route::get('/edit', [AddonConfigController::class, 'edit'])->name('edit');
    Route::patch('/edit', [AddonConfigController::class, 'update'])->name('update');

    Route::name('form-config.')->group(function () {
        Route::get('/{form}/edit', [FormConfigController::class, 'edit'])->name('edit');
        Route::patch('/{form}', [FormConfigController::class, 'update'])->name('update');
        Route::delete('/{form}', [FormConfigController::class, 'destroy'])->name('destroy');
    });

    Route::get('form-fields/{form}', [GetFormFieldsController::class, '__invoke'])->name('form-fields');
    Route::get('merge-fields/{list}', [GetMergeFieldsController::class, '__invoke'])->name('merge-fields');
});
