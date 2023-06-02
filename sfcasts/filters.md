# Filters: Automatically Modify Queries

Thanks to our cool new method, we can filter out discontinued fortune
cookies. But what if we want to apply some criteria like this *globally* to
*every* query to a table? Like, telling Doctrine that *whenever* we query for fortune
cookies, we want to add a `WHERE discontinued = false` to that query.

That sounds *crazy*. And yet, it's *totally* possible. To demonstrate, let's revert
our two templates back to the way they were before. And now... if we go into
"Proverbs"... yep! All 3 fortunes show up again.

## Hello Filters

To apply a "global" WHERE clause, we can create a Doctrine *filter*. In the `src/`
directory, add a new directory called `Doctrine/` for organization. Inside that,
add a new *class* called `DiscontinuedFilter`. Make this extend `SQLFilter`...
then go to Code -> Generate (or "command" + "N" on a Mac) and select "Implement
Methods" to generate the one method we need `addFilterConstraint()`.

Once we have things set up, Doctrine will call `addFilterConstraint()` when it's
building *any* query and pass us some info about *which* entity we're querying
for: that's this `ClassMetadata` thing. It will also pass us the
`$targetTableAlias`, which we'll need in a minute to modify the query.

Oh, and to avoid a deprecation notice, add a `string` return type to the method.

To better see what's happening, let's do our favorite thing and
`dd($targetEntity, $targetTableAlias)`.

## Activating the Filter

But... when we head over and refresh the page... nothing happens! Unlike some
things, filters are *not* activated automatically simply by creating the class.
Activating it is a two-step process.

First, in `config/packages/doctrine.yaml`, we need to tell Doctrine that the
filter exists. Anywhere directly under the `orm` key, add `filters` and then
`fortuneCookie_discontinued`. That string could be anything... and you'll see
how we use it in a minute. Set this to the class: `App\Doctrine\DiscontinuedFilter`.

Easy peasy.

This *is* now *registered* with Doctrine... but as you can see over here, it's
*still* not *called*. The second step is to *activate* it *where* you want it.
In some cases, you might want this `DiscontinuedFilter` to be used on *one*
section of your site, but not on another.

Open the controller... there we go... head up to the homepage and autowire
`EntityManagerInterface $entityManager`. Then, right on top, say
`$entityManager->getFilters()` followed by `->enable()`. Then pass this the *same*
key we used in `doctrine.yaml` - `fortuneCookie_discontinued`. Go grab it...
and paste.

With any luck, every query that we make *after* this line will
use that filter. Head over to the homepage and... yes! It hit it!

And woh! This `ClassMetadata` is a *big* object that knows *all* about our entity.
Down here, apparently, for whatever query we're making first, the table alias -
the alias being used in the query - is `c0_`. Ok! Let's get to work!

## Adding the Filter Logoc

As I mentioned, this will be called for *every* query. So we need to be careful
to *only* add our `WHERE` clause when we're querying for fortune cookies.
To do that, say if `$targetEntity->name !== FortuneCookie::class`, then
`return ''`.

This method returns a `string`... and that string is basically added to
a `WHERE` clause. At the bottom, `return sprintf('%s.discontinued = false')`,
passing `$targetTableAlias` for the wildcard.

Ready to check this out? On the homepage, the "Proverbs" count should go from 3
to 2. And... it does! Check out the query for this. Yup! It has
`t0.discontinued = false` inside of every query for fortune cookies. That's *awesome*!

## Passing Parameters to Filters

Now, one *tricky* thing about these filters is that they are *not* services. So you
*can't* have a constructor... it's just not allowed. If we need to pass something
to this - like some config - we have to do it a different way. For example,
let's pretend that sometimes we want to *hide* discontinued cookies... but other
times, we want to show *only* discontinued ones - the reverse. Essentially, we want
to be  able to toggle this value from `false` to `true`.

To do that, change this to `%s` and fill it in with `$this->getParameter()`...
passing some string I'm making up: `discontinued`. You'll see how that's used in
a minute.

Now, I don't *normally* add `%s` to my queries... because that can allow SQL
injection attacks. In this case, it's okay, but only because we're going to
*entirely* supply this value *ourselves*. This value should *never* come from
the user.

If we head over and try it now... we get a giant error! Yay!

> Parameter 'discontinued' does not exist.

That's true! As soon as you read a parameter, you need to pass that *in* when you
enable the filter. Do that with `->setParameter('discontinued')`... and let's say
`false`.

If we reload now... it's working! What happens if we change this to
`true`? Refresh again and... yep! The number changed! We rule!

## Activating this Globally

Though... you're probably thinking:

> Ryan, dude, yea, this is cool... but can't I enable this filter *globally*...
> without needing to put this code in every controller?

Absolutely! Head back to the controller and comment this out.

When we do that, the number goes back to 3. To enable it globally, head back to
the configuration: we're going to make this a *little* more complicated. Bump
this onto a new line, set that to `class` then set `enabled` to `true`.

And just like that, this will be enabled *everywhere*... though you could still
disable it in specific controllers. Oh, but since we have the parameter, we also
need `parameters`, with `discontinued: false`.

And... there we go! Filters are *cool*.

Next: Let's talk about how to use the handy `IN` operator with a query.
