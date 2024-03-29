# EXTRA_LAZY Relationships

Click back to the homepage with no search query. We still have seven queries
because we're still using our very simple `findAllOrdered()` method... which
doesn't have  the `JOIN`. So... we should add the `JOIN` here too, right? Yep!
Well... *probably*. But I want to show you an *alternative* solution.

Our homepage is unique because we don't *really* need all the `FortuneCookie` data
for each `Category`... the only thing we need is the `COUNT`.

Check out the template: we're not looping over `category.fortuneCookies` and
rendering the actual `FortuneCookie` data. Nope, we're simply *counting* them.
If you think about it, having a giant query that grabs *all* of the `FortuneCookie`
data.... just to count them... isn't the *greatest* thing for efficiency.

[[[ code('b9b9f1787f') ]]]

## Adding fetch: EXTRA_LAZY

If you find yourself in this situation, you can tell Doctrine to be *clever* with
how it loads the relation. Go into the `Category` entity and find the `OneToMany`
relationship for `$fortuneCookies`. At the end, add `fetch:` set to `EXTRA_LAZY`.

[[[ code('8b1ff4cbcd') ]]]

Let's go see what that does. When you refresh, watch the query count. It *stays*
at seven! But if we open up the profiler, the queries *themselves* have changed.
The first one is the same: it queries from `category`. But check out the
others! We have `SELECT COUNT(*) FROM fortune_cookie` over and over! So we *do*
have seven queries, but now each is only selecting the `COUNT`!

When you have `fetch: 'EXTRA_LAZY'` *and* you simply *count* a collection relation,
Doctrine is smart enough to select *just* the `COUNT` instead of querying for
all the data. If we *were* to loop over this collection and start printing out
`FortuneCookie` data, then it *would* still make a *full* query for the data. But
if all we need is to count them, then `fetch: 'EXTRA_LAZY'` is a great solution.

## Custom Query on the Category Show Page

Ok: click into one of the categories. The profiler says that we have two queries.
This is a, sort of, "miniature" N+1 problem. The first query selects a single
`Category`... and the second selects all the fortune cookies *for* this one
category. Let's flex our `JOIN` skills to get this down to *one* query.

Open up `FortuneController` and find the `showCategory()` action. By type-hinting
`Category` on this argument, we're telling *Symfony* to query for the `Category`
*for* us, by using the `{id}`. Normally, I love this! *However*, in this case,
because we want to add a `JOIN` from `Category` to `fortuneCookies`,  we need to
take *control* of that query.

[[[ code('41d5630275') ]]]

Change this so that Symfony passes us the `int $id` directly. Then, autowire
`CategoryRepository $categoryRepository`.

[[[ code('ed4e3086f2') ]]]

Below, do the query manually with `$category = $categoryRepository->`...
calling a new method: `findWithFortunesJoin($id)`. Before we create that, we also
need to add `if (!$category)`, then `throw $this->createNotFoundException()`.
You can give that a message if you want.

Ok, copy the method name, hop over to `CategoryRepository` and say
`public function findWithFortunesJoin(int $id)`, which will return a `Category`
if one is found, else `null`. I'll fix that typo in a minute.

[[[ code('0fbc284bba') ]]]

The query starts like the other.... and we *could* steal some code... but since
we're practicing, let's write it by hand. `return $this->createQueryBuilder()` and
pass our normal `category` alias. Then `->andWhere('category.id = :id')` - I'll
fix that typo in a minutes as well - filling in the wildcard with `->setParameter()`
`id`, `$id`... ideally spelled correctly. Then `->getQuery()`.

[[[ code('1e0d314c7b') ]]]

Until now, we've been fetching *multiple* rows... and so we've used `->getResult()`.
But this time, we want either the *one* result or null if it can't be found. To
do *that*, use `->getOneOrNullResult()`.

[[[ code('f0cb11c346') ]]]

And that's it! That *should* get things working. I'll do a little sanity check over
here, and... *oh*... it would probably help if I typed things correctly. But this
is cool! It recognized that it didn't know what that alias was and gave us a
clear error. And now... it *works*, and we still have two queries.

## Adding a Join

Time for the `JOIN`! We're going from one `Category` to many fortune cookies,
so let's say `->leftJoin()` on `category.` and the property name, which is
`fortuneCookies`. Once again, the order doesn't matter, but above I'll say
`->addSelect('fortuneCookie')`. Oh, and I also need to add `fortuneCookie` as a second
argument inside the `->leftJoin()`: that's the alias.

[[[ code('07444b3b1a') ]]]

So we're aliasing that joined entity to `fortuneCookie` then *selecting* `fortuneCookie`.
Now, we *should* see this query number go from two to one. And... it did!

Here's the takeaway: while there's no need to over-optimize, if you have the N+1
problem, you can solve it by JOINing to the related table *and* selecting its data.

Ok, until now, Doctrine has returned a collection of `Category` objects or a single
`Category` object. That's cool, but what if, instead of entire objects, we just need
some data - like a few columns, a `COUNT`, or a `SUM`? Let's dig into that next.
