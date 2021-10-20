<?php

use Illuminate\Support\Facades\Route;
use Telegram\Bot\Laravel\Facades\Telegram;

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

Route::post('/lutipxfegswfgpoygrzqkiphezeqmfbwhdrswxbazoegtfdskozlbmerpydkexcy/webhook', function () {
    $update = Telegram::commandsHandler(true);
});

Route::get('/setwebhook', function () {
    $response = Telegram::setWebhook(['url' => 'https://bgi-it-bot.herokuapp.com/lutipxfegswfgpoygrzqkiphezeqmfbwhdrswxbazoegtfdskozlbmerpydkexcy/webhook']);
    dd($response);
});

Route::get('/lutipxfegswfgpoygrzqkiphezeqmfbwhdrswxbazoegtfdskozlbmerpydkexcy/sendMessage/{chat_id}',function ($chat_id){
    Telegram::sendMessage([
        'chat_id' => $chat_id,
        'text' => 'Hello world!',
    ]);
    return;
});
