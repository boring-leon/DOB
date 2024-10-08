<?php

final class PersonDob extends DateTime
{
    /**
     * In an actual solution, it would be better to use backed enum cases for each day of the week.
     * They would also be used as parameters for methods.
     * @var array|string[]
     */
    private static array $daysOfWeeks = [
        'Sunday',
        'Monday',
        'Tuesday',
        'Wednesday',
        'Thursday',
        'Friday' ,
        'Saturday'
    ];

    public function __construct(
        $datetime = 'now',
        $timezone = null
    ) {
        parent::__construct($datetime, $timezone);

        $this->throwIfDobIsInTheFuture();
    }

    public function getPlainTextAge(): string
    {
        $age = $this->dateOfBirth()->diff($this->today())->y;

        if ($age < 18) {
            return 'Young';
        } elseif ($age <= 60) {
            return 'Adult';
        } else {
            return 'Senior';
        }
    }

    /**
     * @param string $searchDayOfTheWeek Day of the week represented as in with datetime 'l' format
     */
    public function countWeekDays(string $searchDayOfTheWeek): int
    {
        $this->throwIfInvalidDayOfTheWeek($searchDayOfTheWeek);

        $bornDaysAgo = $this->dateOfBirth()->diff($this->today())->days;

        if ($bornDaysAgo === 0) {
            return 0;
        }

        $extraDaysLivedToToday = $bornDaysAgo % 7;

        $wholeWeeksFromBirth = (int) floor($bornDaysAgo / 7);

        /**
         * Person was born on the same day as today, X whole weeks ago.
         * Since each day of the week is unique, the total number of $searchDayOfTheWeek lived = number of weeks.
         */
        if ($extraDaysLivedToToday === 0) {
            return $wholeWeeksFromBirth;
        }

        /*
         * Person was born X whole weeks ago + a few "extra" days.
         * We will check whether $searchDayOfTheWeek occurs within these extra days, by using the formula
         * day_of_birth_within_current_week + extra_days_count >= check_day_within_current_week
         */

        $lastExtraDayOfWeek = $this->dateOfBirth()
            ->modify("+{$extraDaysLivedToToday} days")
            ->format('l');

        /**
         * Person has lived through a search day within their extra days of the week.
         */
        if (
            $lastExtraDayOfWeek === $searchDayOfTheWeek ||
            $this->isDayAfter($lastExtraDayOfWeek, $searchDayOfTheWeek)
        ) {
            return $wholeWeeksFromBirth + 1;
        }

        return $wholeWeeksFromBirth;
    }

    /**
     * This is done so that methods can be chained nicely
     */
    protected function today(): DateTime
    {
        return new DateTime();
    }

    /**
     * This is done so that so we can safely work underlying DOB data,
     * without actually mutating the object state.
     */
    protected function dateOfBirth(): self
    {
        return clone $this;
    }

    /**
     * @throws InvalidArgumentException
     * @return void
     */
    protected function throwIfDobIsInTheFuture(): void
    {
        $today = new DateTime();

        if ($this > $today) {
            throw new InvalidArgumentException(
                "Provided date of birth {$this->format('Y-m-d')} is in the future."
            );
        }
    }

    /**
     * @throws InvalidArgumentException
     * @param string $dayOfTheWeek
     * @return void
     */
    protected function throwIfInvalidDayOfTheWeek(string $dayOfTheWeek): void
    {
        if (!in_array($dayOfTheWeek, self::$daysOfWeeks)) {
            throw new InvalidArgumentException("Provided day {$dayOfTheWeek} is not valid.");
        }
    }

    protected function isDayAfter(string $day, string $dayCompareTo): bool
    {
        return array_search($day, self::$daysOfWeeks) > array_search($dayCompareTo, self::$daysOfWeeks);
    }
}


/**
 $dob = new PersonDOB('2024-09-29');

 echo $dob->getPlainTextAge(); //Young as of 8.10.2024
 echo PHP_EOL;
 echo $dob->countWeekDays('Monday'); //2 as of 8.10.2024
 echo PHP_EOL;
*/