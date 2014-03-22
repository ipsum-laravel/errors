<?php

// routes de test 403,404,500
Route::get('/error/403', function()
{
    return View::make('IpsumErrors::403');
});

Route::get('/error/404', function()
{
    return View::make('IpsumErrors::404');
});

Route::get('/error/500', function()
{
    return View::make('IpsumErrors::500');
});

Route::get('/error/503', function()
{
    return View::make('IpsumErrors::503');
});