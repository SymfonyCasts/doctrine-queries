# Using GROUP BY to Fetch & Count in 1 Query

*One last challenge*. On the homepage, we have seven queries. That's one to fetch
the categories... and 6 more to get the fortune cookie count for *each* of those
6 categories.

Having 7 queries is... probably not a problem... and you shouldn't worry about
optimizing performance until you actually *see* that there *is* a problem. But let's
*challenge* ourselves to turn these seven queries into *one*.

Let's think: we *could* query for all the categories, `JOIN` over to the related
fortune cookies, `GROUP BY` the category, and then `COUNT` the fortune cookies.
If that doesn't make sense, no worries. We'll see it in action.

## Using a Group By To Select an Object + Other Data

Head over to `FortuneController`. We're on the homepage, and we're using the
`findAllOrdered()` method from `$categoryRepository`. Go find that method... here
it is. We're already selecting from `category`. Now *also*
`->addSelect('COUNT(fortuneCookie.id) AS fortuneCookiesTotal')`. To join and get
that `fortuneCookie` alias, add `->leftJoin('category.fortuneCookies')`, then
`fortuneCookie`. Finally, for this `COUNT` to work correctly, say
`->addGroupBy('category.id')`.

[[[ code('a7b2c44938') ]]]

Okay, let's see what we get! Down here, `dd($query->getResult())`.

[[[ code('513a83c557') ]]]

Previously, this returned an `array` of `Category` objects. If we
refresh... it *is* an array, but it's now an *array of arrays* where the `0` key
is the `Category` object, and then we have this extra `fortuneCookiesTotal`. So...
it selected *exactly* what we wanted! But... it changed the underlying structure.
And it kind of *had* to, right? It needed to *somehow* give us the `Category` object
*and* the extra column behind the scenes.

Remove the `dd` statement. This still returns an `array`... but remove the
`@return` because it no longer returns an array of `Category` objects. We could
also update that to some fancier phpdoc that describes the new structure.

Next, to account for the new return, head to `homepage.html.twig`. We're looping
over `category in categories`... which isn't quite right now: the category is
on this `0` index. Change this to say `for categoryData in categories`... then inside
add `set category = categoryData[0]`. It's ugly, but more on that in a minute.

[[[ code('8012330388') ]]]

Scroll over to the `length`. Instead of reaching across the relationship - 
which *would* work, but would trigger extra queries - use
`categoryData.fortuneCookiesTotal`.

[[[ code('a5929333b3') ]]]

Let's do this! Refresh and... just one query! Woo!

## The Ugly Data Structure

The *worst* part about this is that the structure of our data changed... and now
we have to read this ugly `0` key. I won't do it now, but a *better* solution
would be to leverage a DTO object to hold this. For example, we might create a new
class called `CategoryWithFortuneCount` with two properties - `$category` and
`$fortuneCount`. In this repository method, we could loop over `$query->getResults()`
and create a `CategoryWithFortuneCount` object for each one. Ultimately, our method
would return an array of `CategoryWithFortuneCount`. Returning an array of objects
is much nicer than an *array of arrays*... with some random `0` index.

## Fixing the Search Page

Speaking of that changed structure, if we search for something... we get an error:

> Impossible to access a key "0" on an object of class `Category`.

It's... this line right here. When we search for something, we use the `search()`
method and... surprise! That method doesn't have the new `addSelect()` and
`groupBy()`: it still returns an array of `Category` objects.

[[[ code('e10a64df1c') ]]]

To fix that, create a `private function` down here that can hold the group by:
`addGroupByCategory(QueryBuilder $qb)` and it'll return a `QueryBuilder`. Oh, and
make the argument optional... then create a new query builder if we don't
have one.

[[[ code('45492e9602') ]]]

Ok, head up and steal the logic - the `->addSelect()`, `->leftJoin()`, and
`->addGroupBy()`. Paste that down here. Oh, and `addGroupByCategory()` isn't a
great name: use `addGroupByCategoryAndCountFortunes()`.

[[[ code('caaa7aba76') ]]]

*Awesome*. Above, simplify! Change *this* to `addGroupByCategoryAndCountFortunes()`...
and then we don't need the `->addGroupBy()`, `->leftJoin()`, or `->addSelect()`.

[[[ code('ac5bea4917') ]]]

To make sure *that* part is working, spin over and... head back to the homepage.
That looks good... but if we go forward... still broken. Down in `search()`
add `$qb = $this->addGroupByCategoryAndCountFortunes($qb)`.

[[[ code('47e0b13692') ]]]

And now... *another* error:

> `fortuneCookie` is already defined.
 
Darn! But, yea, that makes sense. We're joining in our new method... and also in
`addFortuneCookieJoinAndSelect()`. Fortunately, we don't *need* this second
call at all anymore: we were joining and selecting to solve the N+1 problem... but
now we have an even *more* advanced query to do that. Copy our new method, delete,
then paste it over the old one.

[[[ code('3dbcd5555f') ]]]

And now... got it! Only 1 query!

Yo friends, we did it! Woo! Thanks for joining me on this magical ride through
all things Doctrine Query. This stuff is just weird, cool and fun. I hope you enjoyed
it as much as I did. If you encounter any *crazy* situation that we haven't thought
about, have any questions, *or* pictures of your cat, we're always here for you
down in the comments. Alright, see you next time!
