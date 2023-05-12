# JOINs

We've got this cool `->andWhere()` method that searches on the `name` *or*
`iconKey` properties of the `Category` entity. But could we *also* search on the
fortune cookie data *inside* each category? Sure!

Let's see how that relation is set up. In `Category`, we have a `OneToMany`
relationship on a property called `$fortuneCookies` over to the `FortuneCookie` entity.

## Thinking about JOINs in Doctrine

If we think about the problem from a database perspective, in order to update our
`WHERE` clause to include `WHERE fortune_cookie.fortune = :searchTerm`, we first
need to `JOIN` to the `fortune_cookie` table.

And that *is* what we're going to do in Doctrine... except with a twist. Instead
of thinking about joining across *tables*, we're going to think about joining
across *entity classes*. This might feel weird at first, but it's super cool.
In this case, we want to `JOIN` across this `fortuneCookies` property over to the
`FortuneCookie` entity.

## Using leftJoin()

Let's do it! Back over in `CategoryRepository`... we can add the join anywhere in
the query. Unlike SQL, the QueryBuilder doesn't care what order you do things.
Add `->leftJoin()` because we're joining from one category to *many* fortune cookies.
Pass this `category.fortuneCookies` then `fortuneCookie`, which will be the *alias*
for the joined entity.

When we say `category.fortuneCookies`, we're referring to the `fortuneCookies`
*property*. The *cool* thing is that... this is all we need! We don't need to tell
Doctrine which entity or table we're joining to... and we don't need the
`ON fortune_cookie.category_id = category.id` that we would normally see in SQL.
We don't need *any* of this because Doctrine already has that info on the
`OneToMany` mapping. We just say "join across this property" and it does the rest!

One thing to keep in mind, which we'll talk more about in a minute, is that, by
joining over to something, we're not *selecting* more data. We're just making the
properties on `FortuneCookie` *available* inside our query. This means we can make
the `->andWhere()` *even longer*. Add `OR fortuneCookie` (using the new alias from
the join) `.fortune` (because `fortune` is the name of the property on `FortuneCookie`
that stores the text) `LIKE :searchTerm`.

Done! Head back to the site. One of my fortunes has the word "conclusion".
Spin over to the homepage, search for "conclusion" and... got it! It looks like we
have at least one match in our "Proverbs" category! Missing accomplished!

But if you click on the database icon of the web debug toolbar... this page has
*two* queries. The first is for the category - it has `FROM category` and includes
the `LEFT JOIN` we just added. The second is `FROM fortune_cookie`.

And if we go to the homepage without searching, there are *seven* queries in total:
one to fetch all the categories... and then an *additional* 6 to find the
fortune cookies for each of the six categories. This is called the N+1 query problem.
Let's talk about it next and fix it with joins.
