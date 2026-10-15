<?php

/*
 * Author Louis Perez
 * Created on 15-10-2026-17h-40m
 * GitHub: https://github.com/louis-perez
 * Copyright 2026
*/

use App\Actions\Goods\ProductDevelopment\Json\SearchProductDevelopmentOptions;
use App\Actions\Goods\ProductDevelopment\UI\CreateProductDevelopment;
use App\Actions\Goods\ProductDevelopment\UI\IndexProductDevelopments;
use App\Actions\Goods\ProductDevelopment\UI\ShowProductDevelopment;
use App\Actions\Goods\ProductDevelopment\UI\ShowProductDevelopmentsBoard;
use Illuminate\Support\Facades\Route;

Route::get('/', IndexProductDevelopments::class)->name('index');
Route::get('/board', ShowProductDevelopmentsBoard::class)->name('board');
Route::get('/create', CreateProductDevelopment::class)->name('create');
Route::get('/json/{scope}', SearchProductDevelopmentOptions::class)->name('json.search')->whereIn('scope', SearchProductDevelopmentOptions::SCOPES);
Route::get('/{productDevelopment:slug}', ShowProductDevelopment::class)->name('show');
