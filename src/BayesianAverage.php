<?php
namespace AssistedMindfulness\BayesianAverage;

class BayesianAverage
{
    /**
     * @var int|float
     */
    protected $countRating;

    /**
     * @var int|float
     */
    protected $sumRating;

    /**
     * @var float|int
     */
    protected float|int $confidenceNumber = 1;

    /**
     * @var float|int
     */
    protected float|int $averageRatingOfAllElements;

    /**
     * @param int $count - The count of quantity of ratings
     * @param int $sum   - The sum of all ratings
     *
     * @return void
     */
    public function __construct(int $count, int $sum)
    {
        $this->countRating = $count;
        $this->sumRating = $sum;

        $this->averageRatingOfAllElements = $this->averageRatingOfAllElements();
    }

    /**
     * The arithmetic average rating of all items (m)
     *
     * @return float
     */
    public function averageRatingOfAllElements(): float
    {
        try {
            return $this->sumRating / $this->countRating;
        } catch (\DivisionByZeroError $exception) {
            return 0;
        }
    }

    public function setAverageRatingOfAllElements(int|float $averageRatingOfAllElements)
    {
        $this->averageRatingOfAllElements = $averageRatingOfAllElements;
    }

    /**
     * @return float|int
     */
    public function getAverageRatingOfAllElements()
    {
        return $this->averageRatingOfAllElements;
    }

    /**
     *  Confidence number
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

    public function setConfidenceNumber(int|float $confidenceNumber)
    {
        $this->confidenceNumber = $confidenceNumber;
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
