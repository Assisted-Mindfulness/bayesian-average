<?php

namespace AssistedMindfulness\BayesianAverage\Tests;

use AssistedMindfulness\BayesianAverage\BayesianAverage;
use Illuminate\Support\Collection;
use PHPUnit\Framework\TestCase;

class BayesianAverageTest extends TestCase
{
    public function testBayesianAverage(): void
    {
        // Item with a large number of ratings
        $itemLargeRating = collect(range(0, 500))->transform(fn () => random_int(4, 5));
        $itemLargeRatingAverage = $itemLargeRating->avg();
        $itemLargeRatingCount = $itemLargeRating->count();

        // Item with a small number of ratings
        $itemSmallRatingAverage = 5;
        $itemSmallRatingCount = 1;

        $c = 100;
        $m = 3.5;

        $bayes = new BayesianAverage();
        $bayes
            ->setConfidenceNumber($c)
            ->setAverageRatingOfAllElements($m);

        $this->assertLessThan(
            $bayes->getAverage($itemLargeRatingAverage, $itemLargeRatingCount), // ~4.3
            $bayes->getAverage($itemSmallRatingAverage, $itemSmallRatingCount)  // ~3.5
        );
    }

    public function testBayesianAverageComparisonWithManuallyCalculatedValues(): void
    {
        $bayes = new BayesianAverage();
        $bayes
            ->setConfidenceNumber(100)
            ->setAverageRatingOfAllElements(3.5);

        // compare the results calculated by the program and manually
        $this->assertEquals(3.6363636363636362, $bayes->getAverage(5, 10));
        $this->assertEquals(4.15, $bayes->getAverage(4.8, 100));
        $this->assertEquals(4.5, $bayes->getAverage(4.6, 1000));
    }

    public function testBayesianAverageForArr(): void
    {
        $data = collect([
            [
                'name'          => "Item A",
                "ratings"       => [5, 4, 3, 4, 3, 2, 4, 3],
                'ratings_count' => 8,
            ],
            [
                'name'          => "Item B",
                "ratings"       => [4, 5, 5, 5, 5, 5, 5, 5, 4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item C",
                "ratings"       => [5],
                'ratings_count' => 1,
            ],
        ]);

        $allRatingsCount = $data->sum('ratings_count');
        $sum = $data->sum(fn ($item) => array_sum($item['ratings']));

        $bayes = new BayesianAverage($allRatingsCount, $sum);

        $this->assertEquals($sum / $allRatingsCount, $bayes->getAverageRatingOfAllElements());

        $this->setConfidenceNumber($bayes, $data);

        // in this example, the confidence number is 1
        $this->assertEquals(1, $bayes->getConfidenceNumber());

        $this->checkAverage($data, $bayes, $sum, $allRatingsCount, 1);
    }

    public function testBayesianAverageEvenSetAndEvenHalf(): void
    {
        $data = collect([
            [
                'name'          => "Item A",
                "ratings"       => [5, 4, 3, 4, 3, 2, 4, 3],
                'ratings_count' => 8,
            ],
            [
                'name'          => "Item B",
                "ratings"       => [4, 5, 5, 5, 5, 5, 5, 5, 4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item C",
                "ratings"       => [5],
                'ratings_count' => 1,
            ],
            [
                'name'          => "Item D",
                "ratings"       => [5, 6],
                'ratings_count' => 2,
            ],
        ]);

        $allRatingsCount = $data->sum('ratings_count');
        $sum = $data->sum(fn ($item) => array_sum($item['ratings']));

        $bayes = new BayesianAverage($allRatingsCount, $sum);

        $this->setConfidenceNumber($bayes, $data);

        //in this example, the confidence number is 1.5
        $this->assertEquals(1.5, $bayes->getConfidenceNumber());

        $this->checkAverage($data, $bayes, $sum, $allRatingsCount, 1.5);
    }

    public function testBayesianAverageEvenSetAndOddHalf(): void
    {
        $data = collect([
            [
                'name'          => "Item A",
                "ratings"       => [5, 4, 3, 4, 3, 2, 4, 3],
                'ratings_count' => 8,
            ],
            [
                'name'          => "Item B",
                "ratings"       => [4, 5, 5, 5, 5, 5, 5, 5, 4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item C",
                "ratings"       => [4, 5, 5, 5, 5, 5, 5, 5, 4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item D",
                "ratings"       => [5],
                'ratings_count' => 1,
            ],
            [
                'name'          => "Item E",
                "ratings"       => [5, 3],
                'ratings_count' => 2,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5, 5, 5],
                'ratings_count' => 3,
            ],
        ]);
        $allRatingsCount = $data->sum('ratings_count');
        $sum = $data->sum(fn ($item) => array_sum($item['ratings']));

        $bayes = new BayesianAverage($allRatingsCount, $sum);

        $this->setConfidenceNumber($bayes, $data);

        // in this example, the confidence number is 2
        $this->assertEquals(2, $bayes->getConfidenceNumber());

        $this->checkAverage($data, $bayes, $sum, $allRatingsCount, 2);
    }

    public function testBayesianAverageOddSetAndEvenHalf(): void
    {
        $data = collect([
            [
                'name'          => "Item A",
                "ratings"       => [4, 5, 5, 5, 5, 5, 5, 5, 4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item B",
                "ratings"       => [4, 5, 5, 5, 5, 5, 5, 5, 4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item C",
                "ratings"       => [5],
                'ratings_count' => 1,
            ],
            [
                'name'          => "Item D",
                "ratings"       => [5, 3],
                'ratings_count' => 2,
            ],
            [
                'name'          => "Item E",
                "ratings"       => [5, 5, 5],
                'ratings_count' => 3,
            ],
        ]);
        $allRatingsCount = $data->sum('ratings_count');
        $sum = $data->sum(fn ($item) => array_sum($item['ratings']));

        $bayes = new BayesianAverage($allRatingsCount, $sum);

        $this->setConfidenceNumber($bayes, $data);
        // in this example, the confidence number is  1.5
        $this->assertEquals(1.5, $bayes->getConfidenceNumber());

        $this->checkAverage($data, $bayes, $sum, $allRatingsCount, 1.5);
    }

    public function testBayesianAverageOddSetAndOddHalf(): void
    {
        $data = collect([
            [
                'name'          => "Item1",
                "ratings"       => [5, 4, 3, 4, 3, 2, 4, 3],
                'ratings_count' => 8,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4, 5, 5, 5, 5, 5, 5, 5, 4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4, 5, 5, 5, 5, 5, 5, 5, 4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5],
                'ratings_count' => 1,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5, 3],
                'ratings_count' => 2,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5, 5, 5],
                'ratings_count' => 3,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4, 5, 5, 5, 5, 5, 5, 5, 4],
                'ratings_count' => 9,
            ],
        ]);

        $allRatingsCount = $data->sum('ratings_count');
        $sum = $data->sum(fn ($item) => array_sum($item['ratings']));

        $bayes = new BayesianAverage($allRatingsCount, $sum);

        $this->setConfidenceNumber($bayes, $data);

        // in this example, the confidence number is  2
        $this->assertEquals(2, $bayes->getConfidenceNumber());
        $this->checkAverage($data, $bayes, $sum, $allRatingsCount, 2);
    }

    public function testWithZeroConfidenceNumber(): void
    {
        $data = collect([

            [
                'name'          => "Item1",
                "ratings"       => [5, 4, 3, 4, 3, 2, 4, 3],
                'ratings_count' => 8,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4, 5, 5, 5, 5, 5, 5, 5, 4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5],
                'ratings_count' => 1,
            ],
            [
                'name'          => "Item1",
                "ratings"       => [],
                'ratings_count' => 0,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [0],
                'ratings_count' => 0,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [],
                'ratings_count' => 0,
            ],
        ]);
        $allRatingsCount = $data->sum('ratings_count');
        $sum = $data->sum(fn ($item) => array_sum($item['ratings']));

        $bayes = new BayesianAverage($allRatingsCount, $sum);

        $this->assertEquals($bayes->getAverageRatingOfAllElements(), $sum / $allRatingsCount);

        $this->setConfidenceNumber($bayes, $data);

        // in this example, the confidence number is  0
        $this->assertEquals(0, $bayes->getConfidenceNumber());

        //With a confidence number of zero, the Bayesian average must be the same as the average.
        $data->each(function ($item) use ($bayes, $sum, $allRatingsCount) {
            $average = count($item['ratings']) ? array_sum($item['ratings']) / count($item['ratings']) : 0;
            $this->assertEquals($bayes->getAverage($average, count($item['ratings'])), $average);
        });
    }

    /**
     * @param \AssistedMindfulness\BayesianAverage\BayesianAverage $bayes
     * @param \Countable|array                                     $data
     *
     * @return void
     */
    protected function setConfidenceNumber(BayesianAverage $bayes, \Countable|array $data): void
    {
        $bayes->setConfidenceNumberForEvenOrOdd(count($data), function ($position) use ($data) {
            $item = collect($data)->sortBy('ratings_count')->values()->get($position / 2);

            return $item['ratings_count'];
        }, function ($position) use ($data) {
            $item1 = collect($data)->sortBy('ratings_count')->values()->get(($position + 1) / 2);
            $item2 = collect($data)->sortBy('ratings_count')->values()->get(($position - 1) / 2);

            return ($item1['ratings_count'] + $item2['ratings_count']) / 2;
        });
    }

    /**
     * @param \Illuminate\Support\Collection                       $data
     * @param \AssistedMindfulness\BayesianAverage\BayesianAverage $bayes
     * @param                                                      $sum
     * @param                                                      $allRatingsCount
     * @param                                                      $confidenceNumber
     *
     * @return void
     */
    protected function checkAverage(Collection $data, BayesianAverage $bayes, $sum, $allRatingsCount, $confidenceNumber): void
    {
        $data->each(function ($item) use ($bayes, $sum, $allRatingsCount, $confidenceNumber) {
            $ratings = collect($item['ratings']);

            $average = $ratings->sum() / $ratings->count();
            $bayesAverage = ($average * $ratings->count() + $confidenceNumber * ($sum / $allRatingsCount)) / ($ratings->count() + $confidenceNumber);

            $this->assertEquals($bayesAverage, $bayes->getAverage($average, $ratings->count()));
        });
    }
}
