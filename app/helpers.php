<?php

if (! function_exists('rupiah')) {
    function rupiah(int|float|null $amount): string
    {
        return 'Rp ' . number_format((int) ($amount ?? 0), 0, ',', '.');
    }
}
