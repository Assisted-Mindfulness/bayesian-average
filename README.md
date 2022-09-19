# Bayesian Average

A Bayesian average is a method of estimating the mean of a population using outside information, especially a pre-existing belief, which is factored into the calculation.


Product reviews and ratings play an important role in consumer decision-making. Online shoppers look for products with the highest ratings. They often read reviews that give details behind the ratings. In the search and discovery context, businesses consider product reviews to be as relevant as product descriptions. Both are relevant for matching users’ queries.

You can use ratings to influence the ranking. For example, when a user types a broad query such as “headphones”, a ratings-based custom ranking ensures that the highest-rated earbuds show up first.

Though this tutorial focuses on star ratings, this solution can work with any product scoring system. For example, you could compute the Bayesian average for the number of up and down votes, or scores based on the number of sales or views.


## The difficulties with calculating a reliable rating

Creating a meaningful ranking strategy based on ratings can be challenging. Is only one rating enough? Or do you need a certain quantity of ratings for the ratings to be reliable? Is a product that receives many mixed ratings, with a range between 1 and 5 (in a 5-star rating system), better than a product with a smaller number of primarily positive ratings? A product that receives only 5 stars is a good choice, but is it better than a popular item with hundreds of 4 stars?

A good example is the query “lion” on a movie streaming site. Should the movie “The Lion King” rank above a higher rated but lesser known film like “The Lion in Winter”? Suppose that “The Lion King” has an average of 4.5 stars but “The Lion in Winter” has a higher average of 4.8. If the 4.5 average rating comes from 10,000 ratings and the 4.8 average from 100 ratings, which movie should show up first? The challenge with any rating system—whether for handbags, electronics, or movies—is that the quantity of ratings is as important as the rating itself. Intuitively, the more ratings received, the more confidence you can have in the rating. But again, how many ratings do you need to have confidence that the rating is meaningful?


### Comparing different ratings rankings

Consider two ways to rank star ratings:

- Use an arithmetic average that adds together all ratings and divides them by the total quantity of ratings. If there are 100 1-star ratings and 10 5-star ratings, the calculation is ((100x1) + (10x5))/ (100+10) = 1.36.
- Use a [Bayesian average](https://en.wikipedia.org/wiki/Bayesian_average) that adjusts a product’s average rating by how much it varies from the catalog average. This favors products with a higher quantity of ratings.
As already suggested, ignoring the quantity of ratings doesn’t help distinguish between items with 10 ratings and 1000 ratings. You need to at least calculate an average that includes the quantity of ratings.

The following image shows three items ranked by different averages. The left side uses the arithmetic average for ranking. The right side uses the Bayesian average.

![image](.github/images/items.png)


Both sides display the arithmetic average in parenthesis just right of the stars. They also display the average used for ranking as `avg_star_rating` and `bayes_avg` respectively, under each item.

By putting Item A at the top, the left side’s ranking is both misleading and unsatisfying. The ranking on the right, based on the Bayesian average, reflects a better balance of rating and quantity of ratings. This example shows how the Bayesian average lowered item A’s average to 4.3 because it measured A’s 10 ratings against B and C’s much larger numbers of ratings. As described later, the Bayesian average left Items B and C unchanged because the Bayesian average affects items with low rating counts much more then those that have more ratings.

In sum, by relativizing ratings in this way, the Bayesian average creates a more reliable comparison between products. It ensures that products with lower numbers of ratings have less weight in the ranking.



## Understanding the Bayesian average

The Bayesian average adjusts the average rating of products whose rating counts fall below a threshold. Suppose the threshold amount is calculated to be 100. That means average ratings with less than 100 ratings get adjusted, while average ratings with more than 100 ratings change only very slightly. This threshold amount of 100 is called a confidence number, because it gives you confidence that averages with 100 or more ratings are more reliable than averages with less than 100 ratings.

This confidence number derives from the catalog’s distribution of rating counts and the average rating of all products. By factoring in ratings counts and averages from the whole catalog, the Bayesian average has the following effect on an item’s individual average rating:

- For an item with a fewer than average quantity of ratings, the Bayesian average lowers its artificially high rating by weighing it down (slightly) to the lower catalog average.
- For an item with a lot of ratings (that is, more than the threshold), the Bayesian average doesn’t change its rating average by a significant amount.


## Installation

You may install Bayesian Average into your project using the Composer package manager:

```bash
composer require assisted-mindfulness/bayesian-average
```


## Usage

Create an instance of the `AssistedMindfulness\BayesianAverage\BayesianAverage` class.

```php
//$allRatingsCount - the count of quantity of ratings, $sum - the sum of all ratings
$bayes = new BayesianAverage($allRatingsCount, $sum);
```
Then you need to set the confidence average.

```php
//$allRatingsCount - the count of quantity of ratings, $sum - the sum of all ratings
$bayes = new BayesianAverage($allRatingsCount, $sum);
```

Then set the confidence mean using one of the following methods:
- `setConfidenceNumber(int|float $confidenceNumber)`: will set the passed argument to a confidence number.
- `setConfidenceNumberForEvenOrOdd(int $count, callable $even, callable $odd):`: - In case of an even `$count` (number of elements), will set the result of executing `$even` as a confidence number; if `$count` is odd, the confidence mean will be set to the result of running `$odd`.



After that, you can get the Bayesian average for the element.
```php
//$average - тhe average rating for this item.
//$countRatings - тhe count ratings for this item.
$bayes->getAverage($average, $countRatings)
```



### Example

```php
<?php


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
]);

$allRatingsCount = $data->sum('ratings_count');
$sum = $data->sum(fn($item) => array_sum($item['ratings']));

$bayes = new BayesianAverage($allRatingsCount, $sum);

$bayes->setConfidenceNumberForEvenOrOdd($data->count(), function ($position) use ($data) {
    $item = $data->sortBy('ratings_count')->values()->get($position / 2);

    return $item['ratings_count'];
}, function ($position) use ($data) {
    $item1 = $data->sortBy('ratings_count')->values()->get(($position + 1) / 2);
    $item2 = $data->sortBy('ratings_count')->values()->get(($position - 1) / 2);

    return ($item1['ratings_count'] + $item2['ratings_count']) / 2;
});

$data->each(function ($item) use ($bayes, $sum, $allRatingsCount) {
    $average = array_sum($item['ratings']) / count($item['ratings']);
    $bayes_avg = round($bayes->getAverage($average, count($item['ratings']), 2));
    printf('Average = %s, Bayesian  average = %s', $average, $bayes_avg);
});



/*
 *Average = 3.5, Bayesian  average = 3.5802469135802 
 *Average = 4.7777777777778, Bayesian  average = 4.7222222222222 
 *Average = 5, Bayesian  average = 4.6111111111111 
 */
```


## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
