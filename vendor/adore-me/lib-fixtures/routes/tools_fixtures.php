<?php
Route::get(
    '/apply/{template}',
    [
        'uses' => 'FixturesController@applyTemplate',
    ]
)->where('template', '[a-zA-Z-_]+');

Route::get(
    '/reset_database',
    [
        'uses' => 'FixturesController@resetDatabase',
    ]
);
