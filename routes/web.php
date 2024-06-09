<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});
Auth::routes();

Route::get('/home', function () {
    return redirect()->route('tasks.index');
})->name('home');

Route::group(['middleware' => 'auth'], function () {
    Route::get('tasks', 'TaskController@index')->name('tasks.index');
    Route::get('all-tasks', 'TaskController@all_task')->name('tasks.all_task');
    Route::post('status-wise-task', 'TaskController@status_wise_task')->name('tasks.status_wise_task');
    Route::get('id-wise-task/{taskId}', 'TaskController@id_wise_task')->name('tasks.id_wise_task');
    Route::delete('remove/{taskId}', 'TaskController@remove')->name('tasks.remove');
    Route::put('task-update/{taskId}', 'TaskController@task_update')->name('tasks.task_update');
    Route::put('status-update/{status_id}', 'TaskController@status_update')->name('tasks.status_update');
    Route::put('status-undo/{status_id}', 'TaskController@undo_update')->name('tasks.undo_update');
    Route::post('tasks', 'TaskController@store')->name('tasks.store');
    Route::put('tasks/sync', 'TaskController@sync')->name('tasks.sync');
    Route::put('status/sync', 'TaskController@status')->name('tasks.status');
    Route::put('tasks/{task}', 'TaskController@update')->name('tasks.update');
});

Route::group(['middleware' => 'auth'], function () {
    Route::post('statuses', 'StatusController@store')->name('statuses.store');
    Route::get('all-statuses', 'StatusController@all_status')->name('statuses.all_status');
    Route::put('title-update/{statusId}', 'StatusController@update')->name('statuses.update');
});
