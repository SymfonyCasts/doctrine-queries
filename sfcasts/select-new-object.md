# SELECTing into a New DTO Object

Having the flexibility to select any data we want is *awesome*. Dealing with
the associative array that we get back is... *less* awesome! I like to work with
objects  whenever possible. Fortunately, Doctrine gives us a simple way to *improve*
this  situation: we query for the data we want... but tell it to give us an *object*.

## Creating the DTO

First, we need to create a new class that will hold the data from our query. I'll
make a new directory called `src/Model/`... but it could be called anything. Call
the class... how about `CategoryFortuneStats`.

The *entire* purpose of this class is to hold the data from this specific query.
So add a `public function __construct()` with a few `public` properties for simplicity:
`public int $fortunesPrinted`, `public float $fortunesAverage`, and
`public string $categoryName`.

*Beautiful*!

Back in the repository, we actually *don't* need any Doctrine magic to use this new
class. We could query for the associative array, then return `new CategoryFortuneStats()`
and pass each key into it.

That's a *great* option, dead simple and then this repository method would return
an object instead of an array. *But*... Doctrine makes this even easier thanks
to a little-known feature.

Add a new `->select()` that will contain *all* of these selects in one. Also add
a `sprintf()`: you'll see why in a minute. Inside, check this out! Say
`NEW %s()` then pass `CategoryFortuneStats::class` for that placeholder. Basically,
we're saying `NEW App\Model\CategoryFortuneStats()`... I just wanted to avoid typing
that long class name.

Inside of `NEW`, grab each of the 3 things that we want to select and paste them,
as *if* we're passing them directly as the first, second and third arguments to our
new class's constructor.

Isn't that cool? Let's `dd($result)` so we can see what it looks like!

## No Aliasing with NEW

If we head over and refresh... oh... I get an error: `T_CLOSE_PARENTHESIS, got 'AS'`.
When we select data into an object, aliasing is no longer needed... or allowed.
And it makes sense: Doctrine will pass whatever this is to the first argument of our
constructor, this to the second argument, and this to the third. Since aliases
don't make sense anymore... remove them.

If we check it now... got it! I love it! We have an object with our data
inside!

Let's celebrate by cleaning up our method. Instead of an `array`, we're
returning a `CategoryFortuneStats`. Also remove the `dd($result)` down here.

Back in the controller, to show off how nice this is, change `$result` to... how
about `$stats`. Then we can use `$stats->fortunesPrinted`, `$stats->fortunesAverage`,
and `$stats->categoryName`.

Now that we've tidied up a bit, let's check to see if this still works. And... it
*does*.

Next: Sometimes queries are *so* complex... the best option is just to write the
darn thing in raw, native SQL. Let's talk about how to do that.
