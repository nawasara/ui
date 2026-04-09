<?php

use Carbon\Carbon;


/** initials */
function initials($name)
{
    $nameParts = explode(' ', trim($name));
    $firstName = array_shift($nameParts);
    $lastName = array_pop($nameParts);

    return
        mb_substr($firstName, 0, 1).
        mb_substr($lastName, 0, 1);
}


function label($string)
{
    return ucwords(str_replace(['-', '_'], ' ', $string));
}

/* date formating */
function date_format_human($value)
{
    Carbon::setLocale('id');

    $carbonDate = Carbon::parse($value);

    // Format datetime menjadi 'd F Y H:i' (contoh: 24 Januari 2024 25:56)
    return $carbonDate->translatedFormat('d F Y');
}

/* Toaster */
function toaster_success($message = 'Berhasil')
{
    session()->flash("toast", [
        "type" => "success",
        "message" => $message
    ]);
}

function toaster_error($message = 'Error')
{
    session()->flash("toast", [
        "type" => "error",
        "message" => $message
    ]);
}
function toaster_warning($message = 'Peringatan')
{
    session()->flash("toast", [
        "type" => "warning",
        "message" => $message
    ]);
}

function toaster_info($message = 'Informasi')
{
    session()->flash("toast", [
        "type" => "info",
        "message" => $message
    ]);
}