# JOINs and addSelect Reduce Queries

When we're on the homepage, we see *seven* queries. We have one to get all the
categories... then additional queries to get all the fortune cookies *for* each
category. We can see this in the profiler. This is the main query `FROM category`...
then each of these down here is selecting fortune cookie data for a specific category:
3, 4, 2, 6, and so on.

## Lazy-Loading Relationships

If you've used Doctrine, you probably recognize what's happening. Doctrine loads its
relationships *lazily*. Let's follow the logic. In `FortuneController`, we
start by querying for an array of `$categories`. 

[[[ code('18107dd0f4') ]]]

In that query, if we look at it, it's *only* selecting *category* data: *not* fortune 
cookie data. But if we go into the template - `templates/fortune/homepage.html.twig` - we loop 
over the categories and eventually call `category.fortuneCookies|length`.

[[[ code('68b4c5bddc') ]]]

## The N+1 Problem

In PHP land, we're calling the `getFortuneCookies()` method on `Category`. But until
now, Doctrine has not *yet* queried for the `FortuneCookie` data for this Category.
However, as *soon* as we access the `$this->fortuneCookies` property, it magically
makes that query, basically saying:

> Give me all the `FortuneCookie` data for this category

Which... it then *sets* onto the property and returns back to us. So it's at
*this moment* inside of Twig when that second, third, fourth, fifth, sixth, and
seventh query is executed.

This is called the "N+1 Problem", where you have "N" number of queries for the
related items on your page "plus one" for the main query. In our case, it's 1
main query for the categories plus 6 more queries to get the fortune cookie data
*for* those 6 categories.

This isn't *necessarily* a problem. It *might* hurt performance on your page...
or be no big deal. But if it *is* slowing things down, we *can* fix it with a `JOIN`.
After all, when we query for the categories, we're *already* joining over to the
fortune cookie table. So... if we just grab the fortine cookie data in the first
query, couldn't we build this whole page *with* that *one* query? The answer is...
totally!

## Selecting the Joined Fields

To see this in action, search for something first. I'm doing this because it
will trigger the `search()` method in our repository, which already has the `JOIN`.
Over here, since we have five results, it made *six* queries.

Okay, we're already *joining* over to `fortuneCookie`. So how can we select its
data? It's delightfully simple. And again, order doesn't matter:
`->addSelect('fortuneCookie')`.

[[[ code('83b2d224ff') ]]]

That's it! Try this thing! The queries went down to one and the page still works!
If you open the profiler... and view the formatted query... yes! It's
joining over to `fortune_cookie` and *grabbing* the `fortune_cookie` data at the
same time. The "N+1" problem is *solved*!

## Where does the Join Data Hide?

But I want to point out one key thing. Because we're inside of
`CategoryRepository`, when we call `$this->createQueryBuilder('category')`, that
automatically adds a `->select('category')` to the query. We know that.

However *now* we're selecting all of the `category` *and* `fortuneCookie` data.
But... our page still works... which must mean that even though we're selecting data
from *two* tables, our query is still *returning* the same thing it did before an
array of `Category` objects. It's not returning some mixture of `category` and
`fortuneCookie` data.

This point can be a bit confusing, so let me break it down. When we call
`createQueryBuilder()`, that actually adds 2 things to our query:
`FROM App\Entity\Category as category` and `SELECT category`. Thanks to the `FROM`,
`Category` is our "root entity" and, unless we start doing something more complex,
Doctrine will try to return `Category` objects. When we
`->addSelect('fortuneCookie')`, instead of returning a mixture of categories and
fortune cookies, Doctrine basically grabs the `fortuneCookie` data and stores it
for later. Then, if we ever call `$category->getFortuneCookies()`, it realizes that
it *already* has that data, so instead of making a query, it uses it.

The really important thing is that when we use `->addSelect()` to grab the
data from a *JOIN*, it does *not* change what our method returns. Though later, we
*will* see times when using `select()` or `addSelect()` *does* change what our
query returns.

Ok, so we just used a JOIN to reduce our queries from 7 to 1. However, because
we're only *counting* the number of fortune cookies for each category, there *is*
another solution. Let's talk about EXTRA_LAZY relationships next.
