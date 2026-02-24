<?php

if (!function_exists('club_id')) {
    function club_id()
    {
        return auth()->check() ? auth()->user()->club_id : null;
    }
}
