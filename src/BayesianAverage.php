<?php

namespace AssistedMindfulness\BayesianAverage;

class BayesianAverage
{
    /**
     * @var float|int
     */
    protected float|int $confidenceNumber = 1;

    /**
     * @var float|int
     */
    protected float|int $averageRatingOfAllElements;

    /**
     * @param int|float $count - The count of quantity of ratings
     * @param int|float $sum   - The sum of all ratings
     */
    public function __construct(
        protected int|float $count = 0,
        protected int|float $sum = 0
    ) {
        $this->averageRatingOfAllElements = $this->averageRatingOfAllElements();
    }

    /**
     * The arithmetic average rating of all items (m)
     *
     * @return float|int
     */
    public function averageRatingOfAllElements(): float|int
    {
        try {
            return $this->sum / $this->count;
        } catch (\DivisionByZeroError $exception) {
            return 0;
        }
    }

    /**
     * @param int|float $averageRatingOfAllElements
     *
     * @return $this
     */
    public function setAverageRatingOfAllElements(int|float $averageRatingOfAllElements):self
    {
        $this->averageRatingOfAllElements = $averageRatingOfAllElements;

        return $this;
    }

    /**
     * @return float|int
     */
    public function getAverageRatingOfAllElements(): float|int
    {
        return $this->averageRatingOfAllElements;
    }

    /**
     * Confidence number
     *
     * @param int      $count
     * @param callable $even
     * @param callable $odd
     *
     * @return self
     */
    public function setConfidenceNumberForEvenOrOdd(int $count, callable $even, callable $odd): self
    {
        $pLast = ($count % 2) === 0
            ? $count / 2 - 1
            : ($count - 1) / 2 - 1;

        $this->confidenceNumber = ($pLast % 2) === 0
            ? $even($pLast)
            : $odd($pLast);

        return $this;
    }

    /**
     * @param int|float $confidenceNumber
     *
     * @return $this
     */
    public function setConfidenceNumber(int|float $confidenceNumber):self
    {
        $this->confidenceNumber = $confidenceNumber;

        return $this;
    }

    /**
     * @return float|int
     */
    public function getConfidenceNumber(): float|int
    {
        return $this->confidenceNumber;
    }

    /**
     * @param int|float $value
     * @param int       $count
     *
     * @return float|int
     */
    public function getAverage(int|float $value, int $count): float|int
    {
        try {
            return ($value * $count + $this->confidenceNumber * $this->averageRatingOfAllElements) / ($count + $this->confidenceNumber);
        } catch (\DivisionByZeroError $exception) {
            return 0;
        }
    }
}
