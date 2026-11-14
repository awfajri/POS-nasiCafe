<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ReportService
{
    public function summary(Carbon $start, Carbon $end): array
    {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }

    public function rowsQuery(Carbon $start, Carbon $end): Builder
    {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }

    public function dailySales(int $days): array
    {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }

    public function monthlySales(int $months): array
    {
        throw new \LogicException(__METHOD__.' belum diimplementasi');
    }
}