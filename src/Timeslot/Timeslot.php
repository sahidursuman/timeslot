<?php

namespace Timeslot;

use Carbon\Carbon;
use DateTime;
use InvalidArgumentException;

class Timeslot implements TimeslotInterface
{
    protected $start;
    protected $hours;
    protected $minutes;
    protected $end;

    /**
     * The Timeslot constructor accepts a DateTime instance, turns it into a
     * Carbon instance and sets start and end time according to the duration
     * provided (default = 1 hour, 0 minutes).
     * If no arguments are passed, it creates a 1-hour timeslot starting at
     * the moment of instantiation (hh:mm:00).
     *
     * @param DateTime|string $start
     * @param int             $hours
     * @param int             $minutes
     */
    public function __construct($start = null, int $hours = 1, int $minutes = 0)
    {
        $start = $this->parseInstance($start);

        $this->start = clone $start;
        $this->hours = $hours;
        $this->minutes = $minutes;

        $this->setStart();
        $this->setEnd();
    }

    /**
     * Convert the $start argument passed to the constructor to a Carbon instance
     * or throw an exception if the argument is invalid.
     *
     * @param DateTime|string $start
     *
     * @throws \Exception
     *
     * @return Carbon\Carbon
     */
    protected function parseInstance($start)
    {
        if (!$start) {
            return Carbon::now();
        }

        if ($start instanceof DateTime) {
            return Carbon::instance($start);
        }

        if (is_string($start)) {
            return Carbon::parse($start);
        }

        throw new InvalidArgumentException('The start time must be an instance of DateTime or a valid datetime string.');
    }

    /**
     * Alternative Timeslot constructor that allows fluent syntax.
     *
     * @param Carbon\Carbon $start
     * @param int           $hours
     * @param int           $minutes
     *
     * @return App\Timeslot
     */
    public static function create($start, $hours = 1, $minutes = 0)
    {
        return new static($start, $hours, $minutes);
    }

    /**
     * Set the start date & time for the timeslot.
     */
    protected function setStart()
    {
        $this->start->second(0);
    }

    /**
     * Set the end date & time for the current instance.
     * A timeslot ends always one second before the
     * duration selected.
     */
    protected function setEnd()
    {
        $this->end = (clone $this->start)
            ->addHours($this->hours)
            ->addMinutes($this->minutes)
            ->subSecond();
    }

    /**
     * Round up start and end time to the start and end of the current hour.
     *
     * @return this
     */
    public function round()
    {
        $this->start->minute(0);
        $this->setEnd();

        return $this;
    }

    /**
     * Return a timeslot identical to the one passed as argument, starting
     * exactly where the first ends. Start time and duration are calculated
     * based on the properties of the timeslot passed.
     *
     * @param Timeslot $timeslot
     *
     * @return Timeslot
     */
    public static function after(Timeslot $timeslot)
    {
        $start = clone $timeslot->start();
        $hours = $timeslot->hours();
        $minutes = $timeslot->minutes();

        return new static($start->addHours($hours)->addMinutes($minutes), $hours, $minutes);
    }

    /**
     * Return a timeslot identical to the one passed as argument, ending exactly
     * where the first starts. Start time and duration are calculated
     * based on the properties of the timeslot passed.
     *
     * @param Timeslot $timeslot
     *
     * @return Timeslot
     */
    public static function before(Timeslot $timeslot)
    {
        $start = clone $timeslot->start();
        $hours = $timeslot->hours();
        $minutes = $timeslot->minutes();

        return new static($start->subHours($hours)->subMinutes($minutes), $hours, $minutes);
    }

    /**
     * Get the start date & time.
     *
     * @return Carbon\Carbon
     */
    public function start()
    {
        return clone $this->start;
    }

    /**
     * Get the end date & time.
     *
     * @return Carbon\Carbon
     */
    public function end()
    {
        return clone $this->end;
    }

    /**
     * Get the hours.
     *
     * @return int
     */
    public function hours()
    {
        return $this->hours;
    }

    /**
     * Get the minutes.
     *
     * @return int
     */
    public function minutes()
    {
        return $this->minutes;
    }

    /**
     * Get an array of start and end date / time.
     *
     * @return array
     */
    public function toArray()
    {
        return [
            'start' => $this->start(),
            'end'   => $this->end(),
        ];
    }

    /**
     * Create a new Timeslot instance based on the current date & time and
     * round it to the current hour's start and end time.
     *
     * @return Timeslot
     */
    public static function now()
    {
        return static::create(Carbon::now())->round();
    }

    /**
     * Return true if the Carbon instance passed as argument is between start
     * and end date & time.
     *
     * @param  Carbon  $datetime
     * @return boolean
     */
    public function has(Carbon $datetime) : bool
    {
        return $datetime->between($this->start(), $this->end());
    }
}
