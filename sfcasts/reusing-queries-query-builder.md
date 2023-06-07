# Reusing Queries in the Query Builder

Open up `CategoryRepository`. We have a few places in here where we `->leftJoin()`
over to `fortuneCookies` and select fortune cookies. In the future, we may need to
do that in even *more* methods... so it would be super-duper if we could *reuse*
that logic instead of repeating it over and over again. Let's do that!

[[[ code('bcf4a27d9d') ]]]

Anywhere inside here, add a new `private function` called
`addFortuneCookieJoinAndSelect()`. This will accept a `QueryBuilder` object (make
sure you get the one from `Doctrine\ORM` - the "Object Relational Mapper"), and let's
call it `$qb`. This will also *return* a `QueryBuilder`.

[[[ code('a17214939b') ]]]

The next step is pretty simple. Go steal the `JOIN` logic from above... and, down
here, say `return $qb`... and paste that... being sure to clean up any spacing
mess that may have occurred.

[[[ code('7bbb6b9429') ]]]

And... done! We can now call this method, pass in the `QueryBuilder`,
and *it* will add the `JOIN` and `SELECT` *for* us.

The result is *pretty* nice. Up here, we can say
`$qb = $this->createQueryBuilder('category')`... then below,
`return $this->addFortuneCookieJoinAndSelect()` passing `$qb`.

[[[ code('6de2fd2c63') ]]]

We create the `$qb`, pass it to the method, it *modifies* it... then also *returns*
the `QueryBuilder`, so we can just chain off of it like normal.

Spin over and try the "Search" feature. And... oh... of course that breaks!
We need to remove this excess code. If we try it now... great success!

To celebrate, repeat that same thing down here. Replace `return` with
`$qb =`... below that, say `return $this->addFortuneCookieJoinAndSelect()`
passing in `$qb`, and then remove `->addSelect()` and `->leftJoin()`.

[[[ code('df154235c0') ]]]

This is for the Category page, so if we click any category... perfect! It's still
rocking.

## Making the QueryBuilder Argument Optional

But... we can even make this even nicer! Instead of requiring the `QueryBuilder`
object as an argument, make it *optional*.

Watch: down here, tweak this so that *if* we have a `$qb`, use it, otherwise,
`$this->createQueryBuilder('category')`. So *if* a `QueryBuilder` was passed in,
use this and call `->addSelect()`, *else* create a fresh `QueryBuilder` and call
`->addSelect()` on *that*.

[[[ code('aec316b433') ]]]

The advantage is that we don't need to initialize our `QueryBuilder` at all
up here... and the same thing goes for the method above.

[[[ code('18ce4274ad') ]]]

But you *can* see how important it is that we're using a *consistent* alias
everywhere. We're referencing `category.name`,`category.iconKey`, and `category.id`...
so we need to make sure that we always create a `QueryBuilder` using that *exact*
alias. Else... things would get explodey.

Let's add one more reusable method: `private function addOrderByCategoryName()`...
because we're probably going to want to *always* order our data in the same way.
Give this the usual `QueryBuilder $qb = null` argument, return a `QueryBuilder`,
and the inside is pretty simple. I'll steal the code above... let me hit "enter"
so it looks a bit better... and then start the same way. Create a `QueryBuilder`
if we need to, and then say `->addOrderBy('category.name')`, followed by
`Criteria::DESC`, which we used earlier in our `search()` method. And yes, we *are*
sorting in *reverse* alphabetical order because, well, honestly I have no idea *what*
I was thinking when I coded that part.

[[[ code('638ca8d5f1') ]]]

To use this, we need to break things up a bit. Start with
`$qb = $this->addOrderByCategoryName()` and pass nothing. Then pass *that* `$qb`
to the second part.

[[[ code('bfa05c0cd0') ]]]

As soon as you have multiple shortcut methods, you can't chain them
*all*... which is a small bummer. But this *does* still allow us to remove the
`->addOrderBy()` down here.

If we try it now... the page still works! And if we try searching for something on
the homepage... that's looking good too!

Next: let's learn about the `Criteria` system: a *really* cool way to efficiently
filter *collection* relationships inside the database, while keeping your code
dead-simple.
