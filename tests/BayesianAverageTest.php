<?php

namespace AssistedMindfulness\BayesianAverage\Tests;

use AssistedMindfulness\BayesianAverage\BayesianAverage;
use PHPUnit\Framework\TestCase;

class BayesianAverageTest extends TestCase
{
    public function testBayesianAverage()
    {
        $item1_ratings = [];
        for ($i = 0; $i < 500; $i++) {
            $item1_ratings[] = rand(4, 5);
        }
        $item1_ratings_avg = collect($item1_ratings)->avg();
        $item1_ratings_count = count($item1_ratings);

        $item2_ratings = [5];
        $item2_ratings_avg = 5;
        $item2_ratings_count = 1;

        $c = 100;
        $m = 3.5;

        $bayes = new BayesianAverage(0, 0);
        $bayes->setConfidenceNumber($c);
        $bayes->setAverageRatingOfAllElements($m);

        $this->assertTrue($bayes->getAverage($item1_ratings_avg, $item1_ratings_count) > $bayes->getAverage($item2_ratings_avg, $item2_ratings_count));
    }

    public function testBayesianAverageComparisonWithManuallyCalculatedValues()
    {
        $data = [
            [
                'name'             => "Item A",
                "avg_rating"       => 5,
                'ratings_count'    => 10,
            ],
            [
                'name'             => "Item B",
                "avg_rating"       => 4.8,
                'ratings_count'    => 100,
            ],
            [
                'name'             => "Item C",
                "avg_rating"       => 4.6,
                'ratings_count'    => 1000,
            ],
        ];

        $bayes = new BayesianAverage(0, 0);
        $bayes->setConfidenceNumber(100);
        $bayes->setAverageRatingOfAllElements(3.5);

        // compare the results calculated by the program and manually
        $this->assertEquals(round($bayes->getAverage($data[0]['avg_rating'], $data[0]['ratings_count']), 3), 3.636);
        $this->assertEquals($bayes->getAverage($data[1]['avg_rating'], $data[1]['ratings_count']), 4.15);
        $this->assertEquals($bayes->getAverage($data[2]['avg_rating'], $data[2]['ratings_count']), 4.5);
    }

    public function testBayesianAverageForArr()
    {
        $data = [
            [
                'name'          => "Item1",
                "ratings"       => [5,4,3,4,3,2,4,3],
                'ratings_count' => 8,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4,5,5,5,5,5,5,5,4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5],
                'ratings_count' => 1,
            ],
        ];
        $allRatingsCount = collect($data)->sum('ratings_count');
        $sum = collect($data)->map(fn ($item) => array_sum($item['ratings']))->sum();

        $bayes = new BayesianAverage($allRatingsCount, $sum);

        $this->assertEquals($bayes->getAverageRatingOfAllElements(), $sum / $allRatingsCount);


        $this->setConfidenceNumber($bayes, $data);
        // in this example, the confidence number is 1
        $this->assertEquals($bayes->getConfidenceNumber(), 1);
        collect($data)->each(function ($item) use ($bayes, $sum, $allRatingsCount) {
            $average = array_sum($item['ratings']) / count($item['ratings']);
            $bayes_avg = ($average * count($item['ratings']) + 1 * ($sum / $allRatingsCount)) / (count($item['ratings']) + 1);
            $this->assertEquals($bayes->getAverage($average, count($item['ratings'])), $bayes_avg);
        });
    }

    public function testBayesianAverageEvenSetAndEvenHalf()
    {
        $data = [
            [
                'name'          => "Item1",
                "ratings"       => [5,4,3,4,3,2,4,3],
                'ratings_count' => 8,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4,5,5,5,5,5,5,5,4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5],
                'ratings_count' => 1,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5,6],
                'ratings_count' => 2,
            ],
        ];
        $allRatingsCount = collect($data)->sum('ratings_count');
        $sum = collect($data)->map(fn ($item) => array_sum($item['ratings']))->sum();

        $bayes = new BayesianAverage($allRatingsCount, $sum);


        $this->setConfidenceNumber($bayes, $data);
        //
        //in this example, the confidence number is 1.5
        $this->assertEquals($bayes->getConfidenceNumber(), 1.5);
        collect($data)->each(function ($item) use ($bayes, $sum, $allRatingsCount) {
            $average = array_sum($item['ratings']) / count($item['ratings']);
            $bayes_avg = ($average * count($item['ratings']) + 1.5 * ($sum / $allRatingsCount)) / (count($item['ratings']) + 1.5);
            $this->assertEquals($bayes->getAverage($average, count($item['ratings'])), $bayes_avg);
        });
    }

    public function testBayesianAverageEvenSetAndOddHalf()
    {
        $data = [
            [
                'name'          => "Item1",
                "ratings"       => [5,4,3,4,3,2,4,3],
                'ratings_count' => 8,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4,5,5,5,5,5,5,5,4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4,5,5,5,5,5,5,5,4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5],
                'ratings_count' => 1,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5,3],
                'ratings_count' => 2,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5,5,5],
                'ratings_count' => 3,
            ],
        ];
        $allRatingsCount = collect($data)->sum('ratings_count');
        $sum = collect($data)->map(fn ($item) => array_sum($item['ratings']))->sum();

        $bayes = new BayesianAverage($allRatingsCount, $sum);


        $this->setConfidenceNumber($bayes, $data);
        // in this example, the confidence number is 2
        $this->assertEquals($bayes->getConfidenceNumber(), 2);
        collect($data)->each(function ($item) use ($bayes, $sum, $allRatingsCount) {
            $average = array_sum($item['ratings']) / count($item['ratings']);
            $bayes_avg = ($average * count($item['ratings']) + 2 * ($sum / $allRatingsCount)) / (count($item['ratings']) + 2);
            $this->assertEquals($bayes->getAverage($average, count($item['ratings'])), $bayes_avg);
        });
    }

    public function testBayesianAverageOddSetAndEvenHalf()
    {
        $data = [
            [
                'name'          => "Item2",
                "ratings"       => [4,5,5,5,5,5,5,5,4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4,5,5,5,5,5,5,5,4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5],
                'ratings_count' => 1,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5,3],
                'ratings_count' => 2,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5,5,5],
                'ratings_count' => 3,
            ],
        ];
        $allRatingsCount = collect($data)->sum('ratings_count');
        $sum = collect($data)->map(fn ($item) => array_sum($item['ratings']))->sum();

        $bayes = new BayesianAverage($allRatingsCount, $sum);


        $this->setConfidenceNumber($bayes, $data);
        // in this example, the confidence number is  1.5
        $this->assertEquals($bayes->getConfidenceNumber(), 1.5);
        collect($data)->each(function ($item) use ($bayes, $sum, $allRatingsCount) {
            $average = array_sum($item['ratings']) / count($item['ratings']);
            $bayes_avg = ($average * count($item['ratings']) + 1.5 * ($sum / $allRatingsCount)) / (count($item['ratings']) + 1.5);
            $this->assertEquals($bayes->getAverage($average, count($item['ratings'])), $bayes_avg);
        });
    }

    public function testBayesianAverageOddSetAndOddHalf()
    {
        $data = [
            [
                'name'          => "Item1",
                "ratings"       => [5,4,3,4,3,2,4,3],
                'ratings_count' => 8,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4,5,5,5,5,5,5,5,4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4,5,5,5,5,5,5,5,4],
                'ratings_count' => 9,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5],
                'ratings_count' => 1,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5,3],
                'ratings_count' => 2,
            ],
            [
                'name'          => "Item3",
                "ratings"       => [5,5,5],
                'ratings_count' => 3,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4,5,5,5,5,5,5,5,4],
                'ratings_count' => 9,
            ],
        ];
        $allRatingsCount = collect($data)->sum('ratings_count');
        $sum = collect($data)->map(fn ($item) => array_sum($item['ratings']))->sum();

        $bayes = new BayesianAverage($allRatingsCount, $sum);


        $this->setConfidenceNumber($bayes, $data);
        // in this example, the confidence number is  2
        $this->assertEquals($bayes->getConfidenceNumber(), 2);
        collect($data)->each(function ($item) use ($bayes, $sum, $allRatingsCount) {
            $average = array_sum($item['ratings']) / count($item['ratings']);
            $bayes_avg = ($average * count($item['ratings']) + 2 * ($sum / $allRatingsCount)) / (count($item['ratings']) + 2);
            $this->assertEquals($bayes->getAverage($average, count($item['ratings'])), $bayes_avg);
        });
    }

    public function testWithZeroConfidenceNumber()
    {
        $data = [

            [
                'name'          => "Item1",
                "ratings"       => [5,4,3,4,3,2,4,3],
                'ratings_count' => 8,
            ],
            [
                'name'          => "Item2",
                "ratings"       => [4,5,5,5,5,5,5,5,4],
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
        ];
        $allRatingsCount = collect($data)->sum('ratings_count');
        $sum = collect($data)->map(fn ($item) => array_sum($item['ratings']))->sum();

        $bayes = new BayesianAverage($allRatingsCount, $sum);

        $this->assertEquals($bayes->getAverageRatingOfAllElements(), $sum / $allRatingsCount);

        $this->setConfidenceNumber($bayes, $data);

        // in this example, the confidence number is  0
        $this->assertEquals($bayes->getConfidenceNumber(), 0);
        //With a confidence number of zero, the Bayesian average must be the same as the average.
        collect($data)->each(function ($item) use ($bayes, $sum, $allRatingsCount) {
            $average = count($item['ratings'])?array_sum($item['ratings']) / count($item['ratings']):0;
            $this->assertEquals($bayes->getAverage($average, count($item['ratings'])), $average);
        });
    }

    public function setConfidenceNumber(BayesianAverage &$bayes, array $data)
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
}
