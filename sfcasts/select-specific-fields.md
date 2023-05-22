# Selecting Specific Fields

Let's add some more stuff to this page! How about the *average* number of fortune
cookies that have been printed for this category? To do that, head back to our query:
it lives in `countNumberPrintedForCategory()`.

## SELECTing the AVG

To get the average, we *could* add a comma then use the `AVG()` function. *Or* we
can use `addSelect()`... which looks a bit better to me. We want the `AVG()` of
`fortuneCookie.numberPrinted` aliased to `fortunesAverage`.

This time, I did *not* use the word `AS`... just doing to demonstrate that the word
`AS` is optional. In Fact, the *entire* `fortunesAverage` or `AS fortunesPrinted`
part is average. But by giving each column a name, it gives us control over the
keys in the final, which we'll see in a minute.

While we're here, instead of printing out the name from the `$category` object,
let's see if we can grab the category name right inside this query. I'll say
`->addSelect('category.name')`.

If you spot a problem with this, you're right! But let's ignore that and see what
happens. `dd($result)` at the bottom.

Previously, this was returning *only* the `fortunesPrinted`. But *now*, we're
selecting *three* things, instead of one. So what will it return now?

The answer is... a gigantic error!

> Error: 'category' is not defined.

Yup - I referenced `category`... but we never *joined* over to that. Let's add that.
Remember: we're querying from the `FortuneCookie` entity and it has a `category`
property. So we're joining over to *one* object. Do that with `->innerJoin()`
passing `->innerJoin('fortuneCookie.category', 'category')`.

## Returning Multiple Columns of Results

If we go refresh the page now... *this* is the error I was expecting:

> The query returned a row containing multiple columns.

This `->getSingleScalarResult()` is perfect when you're returning a single row
*and* a single column. As soon as you return *multiple* columns,
`->getSingleScalarResult()` won't work. To fix that, change to `->getSingleResult()`.
This is basically saying:

> Give me the one row of data that's going to come back.

Try this one more time. *That's* what we want! It return the exact three columns we
selected!

And now... we need to change this method a bit. Update the `int` return to an `array`...
and, down here, remove the `(int)` case entirely and return `$result`.
We can also remove our `dd()`... and you *could* put the `return` up here if you
wanted to.

## Updating our Project to use the Results

Our method is good to go! So let's go back and fix our controller. This
`$fortunesPrinted` isn't right anymore. Change it to `$result` instead. Then...
read that out below with - `$result['fortunesPrinted']`. Copy that, paste, and
send a `fortunesAverage` variable to the template set to the `fortunesAverage` key.
Also pass `categoryName` set to `$result['name']`.

Template time! Over in `showCategory.html.twig`, we have access to the *entire*
`$category` object... which is how we're printing `category.name`. But *now*, we
also have a `categoryName` *variable*. So replace `category.name` with `categoryName`.

There's... no *actual* reason to do that - I'm just proving that we *are* able to
grab extra data in our new query. Though, if we had *also* selected `iconKey`,
then we *could* potentially avoid querying for the `Category` object at all... though
that's probably overkill and makes our code a bit more confusing. Using objects
is best!

Ok, below for the "Print History", hit "enter" and add
`{{ fortunesAverage|number_format }}` then `average`.

*Awesome*. Try this again. If I didn't make any mistakes... got it! Everything
*works*! We have two queries: one for the `category` that's joined over to
`fortune_cookies` and the one that we just made that grabs the `SUM`, `AVG`, and
the `name` with a `JOIN`. Love it!

Getting full entity objects back from Doctrine is the *ideal* situation because...
objects are just really nice to work with. But at the end of the day, if you need
to query for specific data or columns, you can *totally* do that. As we just saw,
Doctrine will return a very simple associative array.

*However*, we *can* go one step further and ask Doctrine to *return* this specific
data *inside* of an object. Let's talk about that *next*.
