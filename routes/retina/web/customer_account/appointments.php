<?php

use App\Actions\Retina\CRM\Appointment\IndexRetinaAppointments;
use Illuminate\Support\Facades\Route;

Route::get('', IndexRetinaAppointments::class)->name('index');
