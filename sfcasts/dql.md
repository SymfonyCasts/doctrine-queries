# Doctrine DQL

Hey there friends! And thanks for joining me for to a tutorial that's all about the
nerdery around running queries in Doctrine. It sounds simple... and it is for a while.
But then you start adding joins, grouping, grabbing only specific data instead of
full objects, counts... and... well... it gets interesting! This tutorial is about
deep-diving into all that good stuff - including running native SQL queries, the Doctrine
Query Language, filtering collections, fixing the "N + 1" problem, and a *ton* more.

Woh, I'm pumped. So let's get rolling!

## Project Setup

To INSERT the most query knowledge into your brain I *highly* recommend coding along
with me. You can download the course code from this page. After you unzip it, you'll
have a `start/` directory with the same code that you see here. There's also a nifty
`README.md` file with all the setup instructions. The *final* step will be to spin
over to your terminal, move into the project, and run:

```terminal
symfony serve -d
```

to start a built-in web server at `https://127.0.0.1:8000`. I'll cheat, click that,
and... say "hello" to our latest initiative - *Fortune Queries*. You see, we have
this side business running a multi-national fortune cookie distribution business...
and this fancy app helps us track all the fortunes we've *bestowed* onto our customers.

It's exactly 2 pages: these are the categories, and you can click *into* one
to see its fortunes... including how many have been printed. This is a Symfony
6.2 project, and at this point, it couldn't be simpler. We have a `Category`
entity, a `FortuneCookie` entity, exactly *one* controller and no fancy queries.

Side note: this project uses MySQL... but almost everything we're going to
talk about will work on Postgres or anything else.

## Creating our First Custom Repository Method

Speaking of that one controller, here on the home page, you can see that we're
autowiring `CategoryRepository` and using the *easiest* way to query for something
in Doctrine: `findAll()`. 

[[[ code('a28ee965b9') ]]]

Our first trick will be super simple, but interesting.
I want to re-order these categories alphabetically by name.
One *simple* way to do this is by changing `findAll()` to `findBy()`. This is normally
used to find items WHERE they match a criteria - something like `['name' => 'foo']`.

But... you can also just leave this empty and take advantage of the second argument:
an order by array. So we could say something like `['name' => 'DESC']`.

*But*... when I need a custom query, I like to create custom repository methods
to centralize everything. Head over to the `src/Repository/` directory and open up
`CategoryRepository.php`. Inside, we can add whatever methods we want. Let's create
a new one called `public function findAllOrdered()`. This will return an `array`...
and I'll even advertise that this is an array of `Category` objects.

[[[ code('12b0cf5ed9') ]]]

Before we fill this in, back here... *call* it: `->findAllOrdered()`.

[[[ code('96068f769d') ]]]

Delightful!

## Hello DQL (Doctrine Query Language)

If you've worked with Doctrine before, you're probably expecting me to use the Query
Builder. We *will* talk about that in a minute. But I want to start even *simpler*.
Doctrine works with a lot of database systems like MySQL, Postgres, MSSQL, and others.
Each of these has an SQL language, but they're not all the same. So Doctrine had to
invent its *own* SQL-like language called "DQL", or "Doctrine Query Language". It's
fun! It looks a *lot* like SQL. The biggest difference is probably that we refer
to classes and properties instead of tables and columns.

Let's write a DQL query by hand. Say `$dql` equals
`SELECT category FROM App\Entity\Category as category`. We're aliasing the
`App\Entity\Category` class to the string `category` in much the same way we might
alias a table name to something in SQL. And over here, by just selecting `category`,
we're selecting *everything*, which means it will return `Category` *objects*.

And that's it! To execute this,  create a `Query` object with
`$query = $this->getEntityManager()->createQuery($dql);`. Then run it with
`return $query->getResult()`.

[[[ code('542dd24d88') ]]]

There's also a `$query->execute()`, and while it doesn't really matter, I prefer
`getResult()`.

When we go over and try that... nothing changes! It *is* working! We just used DQL
*directly* to make that query!

## Adding the DQL ORDER BY

So... what does it look like to add the `ORDER BY`? You can probably guess how
it starts `ORDER BY`! 

The interesting thing is, to order by `name`, we're *not* going to refer to
the `name` *column* in the database. Nope, our `Category` entity has a `$name`
*property*, and *that's* what we're going to refer to. The column is *probably*
also called `name`... but it *could be* called `unnecessarily_long_column_name`
and we would still order by the `name` *property*.

The point is, because we have a `$name` property, over here, we can say
`ORDER BY category.name`.

Oh, and in SQL, using the alias is *optional* - you can say `ORDER BY name`. But
in *DQL*, it's required, so we *must* say `category.name`. Finally, add `DESC`.

[[[ code('5e0bbb1f38') ]]]

If we reload the page now... it's alphabetical!

## The DQL -> SQL Transformation

When we write DQL, behind the scenes, Doctrine converts that to SQL and then
executes it. It looks to see which database system we're using and translates it
into the SQL language *for* that system. We can *see* the SQL with `dd()` (for "dump
and die") `$query->getSQL()`.

[[[ code('5e0bbb1f38') ]]]

And... there it is! That's the *actual* SQL query being executed! It has this ugly
`c0_` alias, but it's what we expect: it grabs every column from that
table and returns it. Pretty cool!

By the way, you can also see the query inside our profiler. If we remove that
debug and refresh... down here, we can see that we're making *seven* queries. We'll
talk about *why* there's seven in a bit. But if we click that little icon... boom!
There's the first query! You can also see a pretty version of it, as well as a version
you can *run*. If you have any variables inside `WHERE` clauses, the runnable version
will fill those in for you.

Next: We normally *don't* write DQL by hand. Instead, we *build* it with the Query
Builder. Let's see what that looks like.
