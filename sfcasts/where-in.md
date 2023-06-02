# WHERE IN()

We have categories for "Pets" and "Love", but if we search up here for "pets love"...
no results! That makes sense. We're searching to see if this string is matching the
`name` or the `iconKey`. Let's make our search smarter to see if we can match
*both* of those categories by searching word by word.

The query for this lives in `CategoryRepository`... on the `search()` method. The
`$term` argument is the string we type in. Down here, let's say
`$termList =` then `explode` that string into an array by splitting on empty spaces.
If you want a *really* rich search, you should use a *real* search system.
But we can do some pretty cool stuff just with the database.

Here's the goal: I want to *also* match results where `category.name` is *in*
one of the words in the array.

## Using the IN

Right after `category.name LIKE :searchTerm`, add `OR category.name IN`. The only
tricky thing about this is the syntax. Add `()`. If we were writing a raw SQL query,
we would write a list here, like `'foo', 'bar'`. But with the query builder, instead,
put a placeholder - like `:termList`. Below pass that in:
`->setParameter('termList', $termList)`.

The *key* thing is that, when you use `IN`, you *will* need the parentheses like
normal... but inside of that, instead of a comma-separated list, you'll set an
*array*. Doctrine will transform that *for* us.

And now... nice! Once you know how it works, it's *just* that easy.

Next: You're probably familiar with the `RAND()` function for MySQL, or maybe the
`YEAR()` function... or one of the many MySQL or PostgreSQL functions that exist.
Well, you might be surprised to learn that some of those *don't* work out of the box.
