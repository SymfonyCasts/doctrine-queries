# Using RAND() or Other Non-Supported Functions

For the heck of it, let's randomize the order of the fortunes on a page. 
Try this category, which has 4.

Start by opening up `FortuneController` and finding `showCategory()`. Right now,
we're querying for the category in the normal way. Then, in our template,
we loop over `category.fortuneCookies`.

Change the query *back* to `->findWithFortunesJoin()`, which lives over here in
`CategoryRepository`. Remember: this joins over to `FortuneCookie` and selects
that data, solving our N+1 problem.

Now that we're doing this, we can *also* control the *order*. Say
`->orderBy('RAND()', Criteria::ASC)`. We're only querying for *one* `Category`...
but this will control the order of the related fortune cookies as well...
which we'll see when we loop over them.

Pretty cool! If we try this... *error*?

> Expected known function, got `RAND`

Wait... `RAND` *is* a known MySQL function. So... why doesn't it work? Ok,
Doctrine supports a *lot* of functions inside DQL, but not *everything*. Why?
Because Doctrine is designed to work with many different types of databases... and
if only one or some databases support a function like `RAND`, then Doctrine *can't*
support it. *Fortunately*, we *can* add this function or any custom function we want
*ourselves* or, really, via a library.

Search for the `beberlei/doctrineextensions` library. This is *awesome*. It allows
us to add a *bunch* of different functions to multiple database types. Go down
here and grab the `composer require` line... but we don't need the `dev-master`
part. Run that!

```terminal-silent
composer require beberlei/doctrineextensions
```

Installing this doesn't change anything in our app... it just adds a bunch of code
that we can *activate* for any functions that we want. To do that, back over in
`config/packages/doctrine.yaml`, somewhere under `orm`, say `dql`. There are a bunch
of different categories under here, which you can read more about in the
documentation. In our case, we need to add `numeric_functions` along with the *name*
of the function, which is `rand`. Set this to the class that will let Doctrine know
what to do: `DoctrineExtensions\Query\Mysql\Rand`.

You definitely don't have to take my word about how this should be set up. Over in
the documentation... there's a "config" link down here... and if you click
on `mysql.yml`, you can see that it describes *all* the different things you can
do and how to activate them.

I'll close that up... refresh, and... got it! Each time we refresh, the results are
coming back in a different order.

Okay, *one more* topic team! Let's finish with a complex `groupBy()` situation
where we select some objects *and* some extra data all at once.
