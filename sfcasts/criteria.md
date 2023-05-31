# Criteria: Filter Relation Collections

On the category show page, we're looping over all of the fortune cookies in that
category. Let's check out that template: `templates/fortune/showCategory.html.twig`.
Here it is: we loop over `category.fortuneCookies` and render some stuff.

But... there's a problem. Open up the `FortuneCookie` entity. It has a
`bool $discontinued` flag. Occasionally, we stop producing a specific fortune cookie.
When we do, we set `discontinued` to true.

Right now, we're looping over *all* of the fortune cookies for a category: including
both current *and* discontinued cookies! But management is really only interested
in *current* fortune cookies. We need a way to *hide* the discontinued ones. How
can we do that?

Over in the controller for this page - `FortuneController` - we *could* create a
separate query from the `$fortuneCookieRepository` with

> WHERE category :category and discontinued = false.

But... that's kind of lame! Right now, looping over the cookies in the template
is deliciously simple. Do we really need to back up to the controller, create
a custom query and pass in the results as a new Twig variable? Is it possible,
instead, to use the `category` object... but filter *out* the discontinued
cookies! Absolutely! And if we do it correctly, we can do it *really* efficiently

The first step is optional, but in the controller, I'm going to change
`->findWithFortunesJoin()` back to just `->find()`. I'm making this change - which
removes the join - *just* so that it's a bit easier for us to see the end result
of what we're about to do.

Doing this doesn't change anything... except that our queries go up to three. That's
one query for the `Category`, our custom query that we're making, and then one query
for all of the fortunes *inside* of this `Category`.

## Adding a Custom Entity Method for Discontinued Cookies

Remember the goal: we want to be able to call *something* on the `Category` object
to get back the related fortune cookies... but hiding the discontinued ones.

Open up the `Category` entity and find the `getFortuneCookies()` method. There it
is. Below that, add a new method called `getFortuneCookiesStillInProduction()`. This,
like the normal method, will return a Doctrine `Collection`. And... just to help
my editor, copy the `@return` doc above to say that this is a `Collection` of
`FortuneCookie` objects.

So... what do we do inside here? We *could* loop over
`$this->fortuneCookies as $fortuneCookie` and create an array of objects that
are *not* discontinued.

But... as soon as we start working with `$this->getFortuneCookies()`, that will
cause Doctrine to query for *every* related fortune cookie. Do you see the problem?
We might be asking Doctrine to query and prepare 100 `FortuneCookie` objects...
even though this final `$inProduction` collection may only contain 10 of them.
What a waste!

What we *really* want to do is tell Doctrine that *when* it makes the query for
the related fortune cookies, it should add an extra `WHERE discontinued = false`.
inside.

## Hello Criteria

But... how the heck do we do that? Doctrine makes that query automatically and
magically somewhere in the background. Whelp, this is where the *criteria system*
comes in handy.

It works like this: say `$criteria = Criteria::` - the one from
`Doctrine\Common\Collections` - `create()`.

This works a bit like the `QueryBuilder`, but it's not *exactly* the same. We
can say `->andWhere()` and then use `Criteria::` again with `expr()->`. This
`expr()` or "expression" functions lets us, sort of, *build* the WHREE clause.
It has methods like `in`, `contains` or `gt` for "greater than". We want `eq()` for
"equals". Inside, say `discontinued`, `false`.

Ok, this, on its own, just creating an object that describes a `WHERE` clause
that could be added to some *other* query. To *use* it,
`return $this->fortuneCookies->matching($criteria)`.

Cool, huh? This basically says:

> Take this collection, but only return the ones that match this criteria.

Yea, as you'll see in a minute, this will *modify* the query to get those fortune
cookies!

To *use* this method, over in `showCategory.html.twig`, instead of looping over
`category.fortuneCookies`, loop over `category.fortuneCookiesStillInProduction`.

Let's try this! Refresh, and... I don't actually know if any of these are
discontinued, but it *did* go from three to two! And the best part? Check out that
query! Here's the first one for the category, here's our custom one... but take a
look at this last query. When we ask for the "fortune cookies still in production",
it queries from `fortune_cookie`, where the `category =` our category *and* where
`t0.discontinued` is false! So it made the most *efficient* query to fetch *just(*
the fortune cookies that we need. That's *amazing*.

## Organizing your Criteria Code in the Reposiotry

Now, one minor downside right now is that... i normally like to keep my query logic
inside of a repository... not in the middle of an entity. Fortunately, we can move
it there.

Becauise this is dealing with fortune cookies, I'm going to open
`FortuneCookieRepository` and, anywhere, add a new `public static function` called...
how about `createFortuneCookiesStillInProductionCriteria()`. This will return a
`Criteria` object.

Now, going the `$criteria` statement from the entity... and return that.

## The Method is Static?

Notice that this is a `static` method... which I don't use *too* often. There are
two reasons for this. First, these `Criteria` objects aren't actually making queries...
and they don't rely on any data or services. And so, this method *can* be static.
Second, and more importantly, we don't have access to the repository object from
inside `Category`. So... if want to call a method on a repository, it needs to
be `static`. This is a special thing I typically do in my repositories *only* for
this criteria situation.

Back in the entity, we can say `$criteria` equals
`FortuneCookieRepository::createFortuneCookiesStillInProductionCriteria()`.

Logic centralization, check! We can also reuse these `Criteria` objects inside
the `QueryBuilder`. Let's see... I don't have a good example... so... in this method,
above, let's pretend I'm creating a `QueryBuilder` with
`$this->createQueryBuilder('fortune_cookie')`. To add the criteria it's...
`->addCriteria(self::createFortuneCookiesStillInProduction)`.

So, even though the criteria system is a bit different than our normal QueryBuilder,
we *can* still reuse them everywhere. Oh, and let's check that things are still
working. We're good!

Okay, on the homepage, we have a similar problem. This says "Proverbs(3)", and if
we click that, there are *two*. This is still showing the count of *all* of the
fortune cookies. What's happening here? Over in `homepage.html.twig`... let's see...
ah, yes. We're looping over `categories`, and then we're calling
`category.fortuneCookies|length` which, as we know, returns *all* of the fortune
cookies. Then we're just counting them. Let's change that to
`fortuneCookiesStillInProduction`.

Back on the homepage, watch this "(3)". It *should* go down to two, and... it *does*.
But that's not even the best part. Open up the query for that. Remember, thanks to
our fetch `EXTRA_LAZY`, since we're only counting the number of fortune cookies,
it knows to make a super fast `COUNT` query. And thanks to the criteria system, it's
selecting `COUNT FROM fortune_cookies WHERE` the `category` = our category *and*
`discontinued = false`. Woh!

Next: We want to hide discontinued fortune cookies from everywhere on our site. Is
there a way that we could hook into Doctrine and add that `WHERE` clause
automatically... *everywhere*? There *is*. It's called *filters*.
