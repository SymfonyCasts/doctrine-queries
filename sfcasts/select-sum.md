# SELECT the SUM (or COUNT)

New goal team! Look over at the `FortuneCookie` entity. One of its properties
is `$numberPrinted`, which is the number of times that we've *ever* printed that
fortune. On the category page, up here, I want to print the *total* number
printed for *all* fortunes in this category.

We *could* solve this by looping over `$category->getFortuneCookies()`... calling
`->getNumberPrinted()` and adding it to some `$count` variable. That would work
as long as we always have a small number of fortune cookies. But the cookie
business is *booming*... and soon we'll have *hundreds* of cookies in each category.
It would be a *huge* slowdown if we queried for 500 fortune cookies
*just* to calculate the sum. Actually, we'd probably run out of memory first!

Surely there's a better way, right? You bet! Do all that work in the *database*
with a *sum query*.

## Overriding the Selected Fields

Let's think: the data we're querying for will ultimately come from the `FortuneCookie`
entity... so open up `FortuneCookieRepository` so we can add a new method there. How
about: `public function countNumberPrintedForCategory(Category $category): int`.

[[[ code('13fef07061') ]]]

The query starts pretty much like they all do. Say
`$result = $this->createQueryBuilder('fortuneCookie')`. By the way, the alias can
be anything. Personally, I try to make them long enough to be unique in my project...
but short enough to not be annoying. More importantly, as soon as you choose an
alias for an entity, stick with it.

[[[ code('e1b87d3878') ]]]

Ok, we know that when we create a QueryBuilder, it will select *all* the data
from `FortuneCookie`. But in this case, we *don't* want that! So, below, say
`->select()` to override that.

Earlier, in `CategoryRepository`, we used `->addSelect()`, which basically says:

> Take whatever we're selecting and *also* select this other stuff.

But this time, I'm purposely using `->select()` so that it *overrides* that and
*only* selects what we put next. Inside, write DQL: `SUM()` a function that
you're probably familiar with followed by `fortuneCookie.` and the name of the
property we want to use - `numberPrinted`. And you don't *have* to do this, but I'm
going to add `AS fortunesPrinted`, which will *name* that result when it's returned.
We'll see that in a minute.

[[[ code('b24e158822') ]]]

## andWhere() with an Entire Entity

Ok, that takes care of the `->select()`. Now we need an `->andWhere()` with
`fortuneCookie.category = :category`... calling `->setParameter()` to fill in the
dynamic `category` with the `$category` object.

[[[ code('2eadb05dee') ]]]

This is interesting too! In SQL, we would normally say something like
`WHERE fortuneCookie.categoryId =` and then the *integer* ID. But in Doctrine, we don't
think about the tables or columns: we focus on the entities. And, there *is* no
`categoryId` property on `FortuneCookie`. Instead, when we say
`fortuneCookie.category` we're referencing the `$category` property in
`FortuneCookie`. And instead of passing *just* the integer ID, we pass the entire
`Category` object. It actually *is* possible to pass the ID, but most of the
time you'll pass the entire object like this.

Okay, let's finish this! Convert this to a query with `->getQuery()`. Below, if you
think about it, we really only want *one* row of results. So let's say
`->getOneOrNullResult()`. Finally, `return $result`.

[[[ code('07ac4a22ff') ]]]

Until now, all of our queries have returned *objects*. Since were selecting just
*one* thing... does that finally change? Let's find out! Add `dd($result)` and
then head  to `FortuneController` to use this. For the show page controller, add
an argument `FortuneCookieRepository $fortuneCookieRepository`. Then below, say
`$fortunesPrinted` equals `$fortuneCookieRepository->countNumberPrintedForCategory()`
passing `$category`.

[[[ code('e07bc76f80') ]]]

Beautiful! Take that `$fortunesPrinted` variable and pass it into Twig as
`fortunesPrinted`.

[[[ code('cf6579e316') ]]]

Finally, find the template - `showCategory.html.twig` - and... there's a table header
that says "Print History". Add some parentheses with `{{ fortunesPrinted }}`.
Add `|number_format` to make this prettier then the word `total`.

[[[ code('8580109145') ]]]

Awesome! Since we have that `dd()`, let's refresh and... look at that! We get an
array back with 1 key called `fortunesPrinted`! Yup, as soon as we start
selecting specific data, we *just* get back that specific data. It's exactly like
you'd expect with a normal SQL query.

If we had said `->select('fortuneCookie')` (which is redundant because that's what
`createQueryBuilder()` already does), that would have given us a `FortuneCookie`
object. But as soon as we're selecting one specific thing, it gets rid of the object
and returns an associative array.

## Using getSingleScalarResult()

Because our method should return an `int`, we *could* complete this by saying
`return $result['fortunesPrinted']`. But if you have a situation where you're
selecting one row of data... and only one *column* of data, there's a shortcut to
*get* that one column: `->getSingleScalarResult()`. We can return *that* directly.

[[[ code('ebd4d16b68') ]]]

I'll keep the `dd()` so we can see it. And... awesome! We get *just* the number!
Well, technically it's a string. If you want to be strict, you can add `(int)`.
And now... got it! We have a nicely formatted total number!

[[[ code('c11ec1f61d') ]]]

Next: Let's select even *more* data and see how that complicates things.
