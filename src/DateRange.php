<?php

namespace FocalStrategy\DateRange;

use Carbon\Carbon;

class DateRange
{
    const DAY = 'day';
    const SUB_DAY = 'sub_day';
    const MONTH = 'month';
    protected $start = null;
    protected $end = null;

    public function __construct(Carbon $start, Carbon $end)
    {
        if ($end->lt($start)) {
            throw new InvalidDateRangeException($start.' is after '.$end);
        }

        // Copy to avoid modifying outside class
        $start = $start->copy()->startOfDay();
        $end = $end->copy()->endOfDay();

        $this->start = $start;
        $this->end = $end;
    }

    public static function resolveRange(string $range, string $now = null) : DateRange
    {
        $valid_ranges = ['year', 'month', 'week', 'day','month_to_date', 'last_month', 'week_to_date','year_to_date','week_commencing', 'next_month', 'next_six_months', 'month_commencing','year_commencing'];

        if (!in_array($range, $valid_ranges)) {
            throw new InvalidDateRangeException($range.' must be one of '.implode(',', $valid_ranges));
        }

        $now = ($now == null) ? Carbon::now() : new Carbon($now);
        $now->endOfDay();

        if ($range == 'year') {
            return self::create($now->copy()->subYear()->startOfDay(), $now->endOfDay());
        } elseif ($range == 'month') {
            return self::create($now->copy()->subMonth()->startOfDay(), $now->endOfDay());
        } elseif ($range == 'month_commencing') {
            return self::create($now->copy()->startOfMonth(), $now->endOfMonth());
        } elseif ($range == 'last_month') {
            return self::create($now->copy()->startOfMonth()->subMonth(), $now->startOfMonth()->subDay()->endOfDay());
        } elseif ($range == 'month_to_date') {
            return self::create($now->copy()->startOfMonth(), $now->endOfDay());
        } elseif ($range == 'year_to_date') {
            return self::create($now->copy()->startOfYear(), $now->endOfDay());
        } elseif ($range == 'year_commencing') {
            return self::create($now->copy()->startOfYear(), $now->endOfYear());
        } elseif ($range == 'week') {
            return self::create($now->copy()->subWeek()->startOfDay(), $now->endOfDay());
        } elseif ($range == 'week_to_date') {
            return self::create($now->copy()->startOfWeek(), $now->endOfDay());
        } elseif ($range == 'week_commencing') {
            return self::create($now->copy()->startOfWeek(), $now->endOfWeek());
        } elseif ($range == 'day') {
            return self::create($now->copy()->startOfDay(), $now->endOfDay());
        } elseif ($range == 'next_month') {
            return self::create($now->copy()->startOfDay(), $now->addMonth(1)->endOfDay());
        } elseif ($range == 'next_six_months') {
            return self::create($now->copy()->startOfDay(), $now->addMonth(6)->endOfDay());
        }

        return self::create();
    }

    public static function create(string $start = null, string $end = null) : DateRange
    {
        $start = ($start == null) ? Carbon::today()
            : Carbon::parse($start)->startOfDay();
        $end = ($end == null) ? Carbon::today()
            : Carbon::parse($end)->endOfDay();

        return new self($start, $end);
    }

    public function getStart() : Carbon
    {
        return $this->start;
    }

    public function getEnd() : Carbon
    {
        return $this->end;
    }

    public function getPrevStart()
    {
        $diff = $this->start->diffInDays($this->end);

        return $this->start->copy()->subDays($diff);
    }

    public function getPrevEnd()
    {
        return $this->start->copy();
    }

    public function getRangeDifferenceInDays() : int
    {
        return $this->start->diffInDays($this->end);
    }

    public function asText() : string
    {
        $formatted_start = $this->start->format('d/m/Y');
        $formatted_end = $this->end->format('d/m/Y');

        if ($formatted_start == $formatted_end) {
            return $formatted_start;
        }
        return 'From '.$formatted_start . ' to '.$formatted_end;
    }

    public function each(string $interval, bool $reverse = false)
    {
        if ($reverse) {
            $cur_date = $this->end->copy()->startOfDay();
            while ($cur_date >= $this->start) {
                yield $cur_date->copy();

                if ($interval == self::MONTH) {
                    $cur_date->subMonth();
                } elseif ($interval == self::DAY) {
                    $cur_date->subDay();
                }
            }
        } else {
            $cur_date = $this->start->copy()->startOfDay();
            while ($cur_date < $this->end) {
                yield $cur_date->copy();

                if ($interval == self::MONTH) {
                    $cur_date->addMonth();
                } elseif ($interval == self::DAY) {
                    $cur_date->addDay();
                }
            }
        }
    }
}
