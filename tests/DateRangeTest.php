<?php

namespace FocalStrategy\DateRange;

use Carbon\Carbon;
use FocalStrategy\DateRange\DateRange;
use PHPUnit\Framework\TestCase;

class DateRangeTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function testConstruct()
    {
        $today = Carbon::parse('2000-01-01');
        $tomorrow = Carbon::parse('2000-01-02');

        $dateRange = new DateRange($today, $tomorrow);

        $this->assertEquals($dateRange->getStart()->format('Y-m-d H:i:s'), '2000-01-01 00:00:00');
    }

    public function testModifyingCarbonOnConstruct()
    {
        $today = Carbon::parse('2000-01-01');
        $tomorrow = Carbon::parse('2000-01-02');

        $dateRange = new DateRange($today, $tomorrow);

        $today->year = 2000;

        $this->assertEquals($dateRange->getStart()->format('Y-m-d H:i:s'), '2000-01-01 00:00:00');
    }

    public function testCreateFromNull()
    {
        $dateRange = DateRange::create();

        $this->assertEquals($dateRange->getStart(), Carbon::today()->startOfDay());
        $this->assertEquals($dateRange->getEnd(), Carbon::today()->endOfDay());
    }

    public function testCreateDate()
    {
        $startDate = '2015-01-01';
        $startTime = '12:23:12';

        $endDate = '2018-01-01';
        $endTime = '09:32:12';

        $dateRange = DateRange::create($startDate.' '.$startTime, $endDate.' '.$endTime);

        $this->assertEquals($dateRange->getStart(), $startDate.' 00:00:00');
        $this->assertEquals($dateRange->getEnd(), $endDate.' 23:59:59');
    }

    /**
     * @expectedException FocalStrategy\DateRange\InvalidDateRangeException
     */
    public function testThrowsIfEndBeforeStart()
    {
        $startDate = '2001-01-01';
        $endDate = '2000-01-01';

        DateRange::create($startDate, $endDate);
    }

    public function testRangeLength()
    {
        $dateRangeLength = 10;

        $start = '2000-01-01';
        $end = '2000-01-11';

        $dateRange = DateRange::create($start, $end);

        $this->assertEquals($dateRange->getRangeDifferenceInDays(), $dateRangeLength);
    }

    public function testResolveYear()
    {
        $dateRange = DateRange::resolveRange('year', '2000-01-01');

        $this->assertEquals($dateRange->getStart(), '1999-01-01 00:00:00');
        $this->assertEquals($dateRange->getEnd(), '2000-01-01 23:59:59');
    }

    public function testResolveMonth()
    {
        $dateRange = DateRange::resolveRange('month', '2000-02-01');

        $this->assertEquals($dateRange->getStart(), '2000-01-01 00:00:00');
        $this->assertEquals($dateRange->getEnd(), '2000-02-01 23:59:59');
    }

    public function testResolveWeek()
    {
        $dateRange = DateRange::resolveRange('week', '2000-02-01');

        $this->assertEquals($dateRange->getStart(), '2000-01-25 00:00:00');
        $this->assertEquals($dateRange->getEnd(), '2000-02-01 23:59:59');
    }

    public function testResolveDay()
    {
        $dateRange = DateRange::resolveRange('day', '2000-02-01');

        $this->assertEquals($dateRange->getStart(), '2000-02-01 00:00:00');
        $this->assertEquals($dateRange->getEnd(), '2000-02-01 23:59:59');
    }

    /**
     * @expectedException FocalStrategy\DateRange\InvalidDateRangeException
     */
    public function testResolveException()
    {
        DateRange::resolveRange('this is not a range', '2000-02-01');
    }
}
