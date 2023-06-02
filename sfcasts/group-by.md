# Using GROUP BY to Fetch & Count in 1 Query

*One last challenge*. On the homepage, we have seven queries. That's one to fetch
all of the categories... and 6 more to get the fortune cookies count for each
of those 6 categories.

Having 7 queries is... probably not a problem... and you shouldn't worry about
optimizing performance until you actually *see* that there *is* a problem. But let's
*challenge* ourselves to turn these seven queries into *one*.

Let's think: we *could* query for all the categories, `JOIN` over to the related
fortune cookies, `GROUP BY` the category, and then `COUNT` the fortune cookies.
If that doesn't make sense, yet no worries. We'll see it in action.

## Using a Group By To Select an Object + Other Data

Head over to `FortuneController`. We're on the homepage, and we're using the
`findAllOrdered()` method from `$categoryRepository`. Go find that method... here
it is. We're already selecting from `category`. Now *also*
`->addSelect('COUNT(fortuneCookie.id) AS fortuneCookiesTotal')`. To join and get
that `fortuneCookie` alias, add `->leftJoin('category.fortuneCookies')`, then
`fortuneCookie`. Finally, for this `COUNT` to work correctly, add
`->addGroupBy('category.id')`.

Okay, let's see what we get! Down here, `dd($query->getResult())`.

As a reminder, this currently returns an `array` of `Category` objects. If we
refresh... it *is* an array, but it's now an *array of arrays* where the `0` key
is a `Category` object, and then we have this extra `fortuneCookiesTotal`. So...
it selected *exactly* what we wanted! But... it changed the underlying structure.
And it kind of *had* to, right? It needed to *somehow* give us the `Category` object
*and* the extra column behind the scenes.

Ok, remove the `dd` statement. This still returns an `array`... but remove the
`@return` because it's no longer an array of `Category` objects. We could also update
that to some fancier phpdoc that describes the structure.

Next, to account for the new return, head to `homepage.html.twig`. We're looping
over `category in categories`... which isn't quite right now - we need to account
for the `0` index. change this to say `for categoryData in categories`... then inside
add `set category = categoryData[0]`. It's ugly, but more on that in a minute.

Scroll over here to the `length`. Instead of reaching across the relationship - 
which *would* work, but trigger extra queries - use
`categoryData.fortuneCookiesTotal`.

Let's do this! Refresh and... just one query! Woo!

## The Ugly Data Structure

The *worst* part about this is that the structure of our data changed... and now
we have to read this ugly `0` key here. I won't do it now, but a *better* solution
would be to leverage a DTO object to hold this. For example, we might create a new
class called `CategoryWithFortuneCount` with two properties - `$category` and
`$fortuneCount`. In thie repository method, we could loop over `$query->getResults()`
and create a `CategoryWithFortuneCount` object for each one. Ultimately, our method
would return an array of `CategoryWithFortuneCount`. Returning an array of objects
is a bit nicer than an *array of arrays*... with some random `0` index.

## Fixing the Search Page

Speaking of that changed structure, if we search for something... we get an error:

> Impossible to access a key "0" on an object of class `Category`.

It's... this line right here. When we search for something, we use the `search()`
method and... surprise! That method doesn't have the new `addSelect()` and `groupBy`:
it still returns an array of `Category` objects.

To fix that, create a `private function` down here that can hold the group by:
`addGroupByCategory(QueryBuilder $qb)` and it ill return a `QueryBuilder`. Oh, and
make the argument optional... then then create a new query builder if we don't
have one.

Ok, head up and steal some the logic - the `->addSelect()`, `->leftJoin()`, and
`->addGroupBy()`. Paste that down here. Oh, and `addGroupByCategory()` isn't a
great name: use `addGroupByCategoryAndCountFortunes()`.

*Awesome*. Above, simply! Change *this* to `addGroupByCategoryAndCountFortunes()`...
and then we don't need the `->addGroupBy()`, `->leftJoin()`, or `->addSelect()`.

To make sure *that* part is working, spin over and... head back to the homepage.
That still looks good... but if we go forward... still broken. Down in `search()`
add `$qb = $this->addGroupByCategoryAndCountFortunes($qb)`.

And now... *another* error:

> `fortuneCookie` is already defined.

That makes sense. We're joining our new method... and also in
`addFortuneCookieJoinAndSelect()`. Fortunately, we don't really *need* this second
call at all anymore: we were joining and selecting to solve the N+1 problem... but
now we have an even *more* advanced query for this page. Copy our new method, delete,
then paste it over the old one.

And now... got it! Only 1 query!

Yo friends, we did it! Woo! Thanks for joining me for this magical ride through
all things Doctrine Query. This stuff is just weird, cool and fun. I hope you enjoyed
it as much as I did. If you encounter some *crazy* situation that we haven't thought
about, have any questions, *or* pictures of your cat, we're always here for you
down in the comments. Okay, see you next time!
