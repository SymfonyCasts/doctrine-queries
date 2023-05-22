# SELECTing into a New DTO Object

Having the flexibility to select any data we want is *awesome*. Dealing with
associative arrays that we get back is... *less* awesome. I like to work with objects
whenever possible. Fortunately, Doctrine gives us a simple way to *improve* this
situation: we query for the data we want... but tell it to give us an object.

## Creating the DTO

First, we need to create a new class that will hold the data from our query. I'll
make a new directory called `src/Model/`... but it could be called anything. Call
the class... how about `CategoryFortuneStats`.

The *entire* purpose of this class is to hold the data from this specific query.
So add a `public function __construct()` with a few `public` columns for simplicity:
`public int $fortunesPrinted`, `public float $fortunesAverage`, and
`public string $categoryName`.

*Beautiful*!

Back in the repository, we actually *don't* need any Doctrine magic to use this new
class. We could query for the associative array, then return `new CategoryFortuneStats()`
and pass each key into it.

That's a *great* option, dead simple and then this repository method would return
an object instead of an array. *But*... Doctrine can make this even easier thanks
to a little-known feature.

Add a new `->select()` that will contain *all* of these selects in one. Also add
a `sprintf()` - you'll see why in a minute. Then, inside, check this out! Say
`NEW %s()`. For the `%s`, pass `CategoryFortuneStats::class`. Basically, we're saying
`new App\Model\CategoryFortuneStats()`... I just wanted to avoid typing that long
class name.

Inside of `NEW`, grab each of the 3 things that we want to select and paste them
as if we're passing them directly as the first, second and third arguments to our
new class's constructor.

Ok! Let's `dd($result)` so we can see what that looks like!

If we head over and refresh... oh... I get an error: `T_CLOSE_PARENTHESIS, got 'AS'`.
When we're selecting data into an object, it doesn't make sense for us to alias
these anymore. Doctrine will whatever this is to the first argument of our
constructor, this to the second argument, and this to the third. Aliases don't make
sense anymore... so remove them.

If we check it now... got it! Check that out! We have our object with our data
inside! *Sweet*.

Now we can head back over and clean up our method. Instead of an `array`, we're
returning `CategoryFortuneStats`. Also remove the `dd($result)` down here.

Back in the controller, to show off how nice this is, change `$result` to... how
about `$stats`. Then we can use `$stats->fortunesPrinted`, `$stats->fortunesAverage`,
and `$stats->categoryName`.

Now that we've tidied up a bit, let's check to see if this still works. And... it
*does*.

Next: Sometimes queries are *so* complex... the best option is just t write raw native
SQL queries. Let's talk about how to do that.
