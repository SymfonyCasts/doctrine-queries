# Filters: Automatically Modify Queries

We now know how, thanks to our cool method, we can filter out discontinued fortune
cookies. But what if we want to apply some criteria *globally* to *every* query to
some table? Like, telling Doctrine that *whenever* we query for fortune cookies,
we want to add a `WHERE is_discontinued = false` to that query.

That sounds *crazy*. And yet, it's *totally* possible. To demonstrate, let's revert
our two templates back to the way they were before. And now... if we go into
"Proverbs"... yep! All 3 fortunes show up again.

## Hello Filters

To apply a "global" WHERE clause, we can create a Doctrine *filter*. In the `src/`
directory, add a new directory called `Doctrine/` for organization. Inside that,
create a new class called `DiscontinuedFilter`. Make this extend `SQLFilter`...
then go to Code -> Generate (or "command" + "N" on a Mac) and select "Implement
Methods". This requires us to have one method: `addFilterConstraint()`.

This is pretty cool. Once we have things set up, Doctrine will call
`addFilterConstraint()` when it's building a query and pass us some info about
*which* entity we're querying for: that's this `ClassMetadata` thing. It will
also pass us the `$targetTableAlias`, which we'll need in a minute to modify
the query.

Oh, and to avoid a deprecation notice add a `string` return type to the method.

to better see see what's happening, let's do our favorite thing and
`dd($targetEntity, $targetTableAlias)`.

## Activating the Filter

But... when we head over and refresh the page... nothing happens! Unlike some
things, filters are *not* activated automatically simply by creating the class.
Activating it is a two-step process.

First, in `config/packages/doctrine.yaml`, we need to tell Doctrine that the
filter exists. Anywhere directly under the `orm` key, add `filters` and then
`fortuneCookie_discontinued`. That could be anything... and you'll see where we
use it in a minute. Then, set the class to `App\Doctrine\DiscontinuedFilter`.

*Easy peasy*.

This *is* now *registered* with Doctrine... but as you can see over here, it's
*still* not *called*. The second step is to *activate* it *where* you want it.
In some cases, you might want this `DiscontinuedFilter` to be used on *one*
section of your site, but not on another.

Open the controller... there we go... head up to the homepage and autowire
`EntityManagerInterface $entityManager`. Then, right on top, say
`$entityManager->getFilters()` followed by `->enable()`. Pass *this* the same key
that we used in `doctrine.yaml` - `fortuneCookie_discontinued`. Go grab it...
and paste.


With any luck, every query to fortune cookies that we make *after* this line will
use that filter. Head over to the homepage and... yes! It hit it!

And woh! This `ClassMetadata` is a *big* object that knows *all* about our entity.
And down here, apparently, for whatever query we're making first, the table alias -
the alias being used in the query - is `c0_`. So let's get to work!

## Adding the Filter Logoc

Now that the filter is activated, it will be called whenever we query for *any* entity.
So the first thing we want to do is say `if`
`$targetEntity->name !== FortuneCookie::class`, then we're querying for something
else and we'll `return ''`.

This method returns a `string`... and basically whatever we return will be added
to a `WHERE` clause. At the bottom, `return sprintf('%s.discontinued = false')`,
passing `$targetTableAlias` for the wildcard.

Ready to check this out? On the homepage, the "Proverbs" count should go from 3
to 2. And... it does! Check out the query for this. Yup! It has
`t0.discontinued = false` inside of every query for fortune cookies. That's *awesome*.

## Passing Parameters to Filters

Now, one *tricky* thing about these filters is that they are *not* services. So you
*can't* have a constructor... it's just not allowed. If we need to pass something
into this - like some configuration - we have to do it a different way. For example,
let's pretend sometimes we want to *hide* discontinued cookies... but other times,
we want to show *only* discontinued ones - the reverse. Essentially, we want to be
able to change this value from `false` to `true`.

to do that, change this to `%s` and fill it in with `$this->getParameter()`...
passing some string I'm making up: `discontinued`. You'll see how that's used in
a minute.

Now, I don't *normally* add `%s` inside my queries... because that can allow SQL
injection attacks. In this case, it's okay, because we're going to supply this value
*ourselves*. This value should *never* come from the user.

If we head over and try it now... we get a giant error! Yay!

> Parameter 'discontinued' does not exist.

That's true! As soon as you read a parameter, you need to pass that *in* when you
enable the filter. Do that with `->setParameter('discontinued')`... and let's say
`false`.

If we reload now... it's working! What happens if we change this to
`true`? Refresh again and... yep! The number changed! We rule!

## Activating this Globally

Though... you're probably thinking:

> Ryan, dude, yea, this is cool... but can't I enable this filter *globally*?
> Without needing to put this code in every controller?

Absolutely! Head back to the controller and comment this out.

When we do that, the number goes back to 3. To enable it globally, head back to
the configuration... we're going to make this a *little* more complicated. Bump
this onto a new line, set that to `class` then set `enabled` to `true`.

And just like that, this will be enabled *everywhere*... though you could still
disable it in specific controllers. Oh, but since we have the parameter, we also
need `parameters`, with `discontinued: false`.

And... there we go! Filters are *pretty* sweet.

Next: Let's talk about how to use the handy `IN` operator with a query.
