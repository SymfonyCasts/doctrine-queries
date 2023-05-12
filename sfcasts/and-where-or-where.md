# andWhere() and orWhere()

Our site has this nifty search box, which... *doesn't* work. If I hit "enter"
to search for "lunch", it *does* add `?q=lunch` to the end of the URL... but the
results don't change. Let's hook this thing up!

## Grabbing the Search Query Parameter

Spin over and find our controller: `FortuneController`. To read the query
parameter, we need Symfony's `Request` object. Add a new argument - it doesn't
matter if it's first or last - type-hinted with `Request` - the one from Symfony -
hit "tab" to add that `use` statement, and say `$request`. We can get the search
term down here with `$searchTerm = $request->query->get('q')`.

We're using `q`... just because that's what I chose in my template... you can see
it down here in `templates/base.html.twig`. This is built with a very simple form
that includes `<input type="text"`, `name="q"`. So we're reading the `q` query
parameter and setting it on `$searchTerm`.

Below, `if` we have a `$searchTerm`, set `$categories` to
`$categoryRepository->search()` (a method we're about to create) and pass
`$searchTerm`. If we *don't* have a `$searchTerm`, reuse the query logic that
we had before.

## Adding a WHERE Clause

Awesome! Let's go create that `search()` method!

Over in our repository, say `public function search()`. This will take a `string
$term` argument and return an `array`. Like last time, I'll add some
PHPDoc that says this returns an array of `Category[]` objects. Remove the `@param`...
because that doesn't add anything.

Ok: our query will start like before... though we can get fancier and `return`
*immediately*. Say `$this->createQueryBuilder()` and use the same `category` alias.
It's a good idea to always use the same alias for an entity: it'll help us later
to *reuse* parts of a query builder.

For the `WHERE` clause, use `->andWhere()`. There *is* also a `where()` method... but
I don't think I've ever used it! And... you shouldn't either. Using `andWhere()`
is always ok - even if this is the *first* `WHERE` clause... and we don't really
need the "and" part. Doctrine is smart enough to figure that out.

## andWhere() vs where()

What's wrong with `->where()`? Well, if you added a `WHERE` clause to
your `QueryBuilder` earlier, calling `->where()` would *remove* that and *replace*
it with the new stuff... which probably isn't what you want. `->andWhere()` always
*adds* to the query.

Inside say `category`, and since I want to search on the `name` property of the
`Category` entity, say `category.name =`. This next part is *very* important. Never
ever, *ever* add the dynamic part directly to your query string. This opens you up
for SQL injection attacks. Yikes. *Instead*, any time you need to put a dynamic part in
a query, put a placeholder instead: like `:searchTerm`. The word `searchTerm`
could be anything... and you fill it in by saying
`->setParameter('searchTerm', $term)`.

Perfecto! The ending is easy: `->getQuery()` to turn that into a `Query` object
and then `->getResult()` to *execute* that query and return the array of `Category`
objects.

*Sweet*! If we head over and try this... got it!

## Making the Query Fuzzy

*But* if we take off a few letters and search again... we get *nothing*!
Ideally, we want the search to be fuzzy: matching *any* part of the name.

And that's easy to do. Change our `->andWhere()` from `=` to `LIKE`... and down here,
for `searchTerm`... this looks a bit weird, but add a percent before and after
to make it fuzzy on both sides.

If we try it now... eureka!

## Being Careful with orWhere

But let's get tougher! Every category has its own icon - like `fa-quote-left` or the
one below it has `fa-utensils`. This is *also* a string that's stored in the database!

Could we make our search *also* search on that property? Sure! We just need to add
an `OR` to our query.

Down here, you might be tempted to use this nice `->orWhere()` passing `category.`
with the name of that property... which... if we look in `Category` real quick...
is `$iconKey`. So `category.iconKey LIKE :searchTerm`.

And yes, we *could* do that. But don't! I recommend *never* using `orWhere()`.
Why? Because... things can get weird. Imagine we had a query like this:
`->andWhere('category.name LIKE :searchTerm')`,
`->orWhere('category.iconKey LIKE :searchTerm')`
`->andWhere('category.active = true')`.

Do you see the problem? What I'm *probably* trying to do is search for categories...
but only every match *active* categories. In reality, if the `searchTerm`
matches `iconKey`, a `Category` will be returned whether it's active or not.
If we wrote this in SQL, we would include parenthesis around the first two parts
to make it behave. But when you use `->orWhere()`, that doesn't happen.

So what's the solution? Always use `andWhere()`... and if you need an `OR`, put
it right inside that! Yup, what you pass to `andWhere()` is DQL, so we can say
`OR category.iconKey LIKE :searchTerm`.

That's it! In the final SQL, Doctrine will put parentheses around this `WHERE`.

Let's try it! Spin over and try searching for "utensils". I'll type part of the
word  and... got it! We're matching on the `iconKey`!

Oh, and to keep this consistent with the normal homepage, let's include
`->addOrderBy('category.name', 'DESC')`.

Now, if we go to the homepage and just type the letter "p" in the search bar, yup!
It's sorting alphabetically.

And if you have any doubts about your query, you can always head into the Doctrine
profiler to see the formatted version. That's exactly what we expected.

Next: Let's extend our query, so we can search on the *fortune cookies* that 
are *inside* each category. To do that, we'll need a `JOIN`.
