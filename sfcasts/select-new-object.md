# SELECTing into a New DTO Object

Having the flexibility to select any data we want is *awesome*. Dealing with associative arrays that we get back is... *less* awesome. I typically try to deal with objects whenever possible. Fortunately, Doctrine gives us a simple way to improve this situation, where we can take this data and just throw it into an object. Convenient!

First, we need to create a new class that's going to hold this data. I'll make a new directory called `/src/Model`, but it could be called anything. Then I'm going to call this class... how about `CategoryFortuneStats.php`. The whole purpose of this class is to hold that data, so I'm going to add `public function __construct()`. Then we're going to give it a couple of `public` columns for simplicity: `public int $fortunesPrinted`, `public float $fortunesAverage`, and `public string $categoryName`. *Beautiful*.

Okay, one of the things we could do here is just `return` this result, create one of those `new CategoryFortuneStats()` objects, and just pass in that data that we selected above. That's a *great* option, and dead simple, since it would allow us to return an object from this method instead of an array. *But* Doctrine actually makes it even easier than that, and it's a little-known feature.

Let's add a new `->select()` up here that's going to contain all of these selects together. and we'll also add `sprintf`. You'll see why in a second. Then, inside, check this out! We're going to say `NEW %s()`, and then we'll just pass in the arguments to that method. For the `%s`, we're going to pass in `CategoryFortuneStats::class`. Basically, what we're doing is saying `new App\Model\CategoryFortuneStats`. I just wanted to avoid typing that ugly class name inside of here, so I used `%s` instead.

Inside of `NEW`, we're going to grab the three columns that are associated with that. So we're selecting that data... *that* data... and this data down here. And down here, let's add `dd($result)` so we can see what that looks like.

If we head over and refresh... oh... I get an error: `T_CLOSE_PARENTHESIS, got 'AS'`. When we're creating into an object, it doesn't make sense for us to alias those things anymore. It's going to pass whatever this is to the first argument of our constructor, this to the second argument, and this to the third. Aliases don't make sense anymore, so we're going to remove them. If we check it now... got it! Check that out! We have our object with our data in it! *Sweet*.

Now we can head back over and clean up our method. We're not returning an `array` anymore. We're returning `CategoryFortuneStats`. I'll also remove `dd($result)` down here. Then, over in our controller, to show off how nice this is, I'll change this `$result` to... how about `$stats`. Down here, this is going to be `$stats->fortunesPrinted`, `$stats->fortunesAverage`, and `$stats->categoryName`. Now that we've tidied up a bit, let's check to see if this still works. And... it *does*.

Next: Sometimes queries are so complex, you'll need to write raw native SQL queries. Let's talk about how to do that.
