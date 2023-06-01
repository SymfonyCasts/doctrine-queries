# Raw SQL Queries

The QueryBuilder is fun to use *and* powerful. But if you're writing a *super*
complex query... it might be tough to figure out how to transform it into the QueryBuilder
format. If you find yourself in this situation, you can always resort to just...
writing *raw* SQL! I wouldn't make this my *first* choice - but there's no *huge*
benefit to spending hours adapting a well-written SQL query into a query builder.

## The Connection Object

Let's see how raw SQL queries work. To start, comment out the `->createQueryBuilder()`
query. Then, we need to fetch the low-level Doctrine `Connection` object. We can
get that with `$conn = $this->getEntityManager()->getConnection()`. Toss `dd($conn)`
onto the end so we can see it.

Head over, refresh and... awesome! We get a `Doctrine\DBAL\Connection` object.

The Doctrine library is actually *two* main parts. First there's a lower-level part
called "DBAL", which stands for "Database Abstraction Library". This acts as a
wrapper around PHP's native PDO and adds some features on top of it.

The *second* part of Doctrine is what we've been dealing with so far: it's the
higher-level part called the "ORM "or "Object Relational Mapper". That's when you
query by selecting classes and properties... and get back objects.

For this raw SQL query, we're going to deal with the lower-level `Connection` object
directly.

## Writing & Executing the Query

Say `$sql = 'SELECT * FROM fortune_cookie'`. That's as *boring* as SQL queries can
get. I used `fortune_cookie` for the table name because I know that, by default,
Doctrine *underscores* my entities to make table names. 

Now that we have the query string, we need to create a `Statement` with
`$stmt = $conn->prepare()` and pass `$sql`.

This creates a `Statement` object... which is kind of like the `Query` object we
would create with the `QueryBuilder` by saying `->getQuery()` at the end. It's...
just an object that we'll use to execute this. Do that with
`$result = $stmt->executeQuery()`.

*Finally*, to get the actual *data* off of the result, say `dd(result->)`...
and there are a number of methods to choose from. Use `fetchAllAssociative()`.

This will fetch all the rows and give each one to us as an *associative* array.

Watch: head back over and... perfect! We get 20 rows for each of the 20 fortune
cookies in the system! This is the raw data coming from the database.

## A More Complex Query

Okay, let's rewrite this entire QueryBuilder query up here in raw SQL. To save time,
I'll paste in the final product: a *long* string... with nothing particularly special.
We're selecting `SUM`, `AS fortunesPrinted`, the `AVG`, `category.name`, `FROM
fortune_cookie`, and then we do our `INNER JOIN` over to `category`.

The big difference is that, when we do a `JOIN` with the QueryBuilder, we can just
join across the relationship... and that's all we need to say. In raw SQL, of course,
we need to help it by *specifying* that we're joining over to `category` and
describe that we're joining on `category.id = fortune_cookie.category_id`.

The rest is pretty normal... except for `fortune_cookie.category_id = :category`.
Even though we're running raw SQL, we're *still* *not* going to concatenate
dynamic stuff directly into our query. That's a *huge* no-no, and, as we know,
opens us up to SQL injection attacks. Instead, stick with these nice placeholders
like `:category`. To fill that in, down where we execute the query, pass
`'category' =>`. But this time, instead of passing the entire `$category` object
like we did before, this is raw SQL, so we need to pass `$category->getId()`.

Ok! Spin over and check this out. Got it! So writing raw SQL doesn't look as
awesome... but if your query is complex enough, don't hesitate to try this.

## Using bindValue()

By the way, instead of using `executeQuery()` to pass the `category`, we *could*,
replace that with `$stmt->bindValue()` to bind `category` to `$category->getId()`.
That's going to give us the same results as before, so your call.

But, hmm, I'm realizing now that the result is an array inside another array.
What we *really* want to do is return *only* the associative array for the *one*
result. No problem: instead of `fetchAllAssociative()`, use `fetchAssociative()`.

And now... beautiful! We get just that first row.

## Hydrating into an Object

Now, you *may* remember that our method is *supposed* to return a
`CategoryFortuneStats` object that we created earlier. Can we convert our array
result into that object? Sure! It's not fancy, but easy enough.

We could return a `new CategoryFortuneStats()`... and then grab the array keys
from `$result->fetchAssociative()`... and pass them as the correct arguments.

Or, you can be even *lazier* and use the spread operator along with named arguments.
Check it out: the arguments are called `fortunesPrinted`, `fortunesAverage`, and
`categoryName`. Over *here*, they are `fortunesPrinted`, `fortunesAverage`, and
`name`... not `categoryName`. Let's fix that. Down here, add `as categoryName`.
And then... yep! It's called `categoryName`.

*Now* we can use named arguments. Remove the `dd()` and the other return.
To `CategoryFortuneStats`, pass `...$result->fetchAssociative()`.

This will grab that array and spread it out across those arguments so that we have
three *correctly* named arguments... which is just kind of fun.

And now... our page works!

Next: Let's talk about organizing our repository so we can *reuse* parts of our
queries in *multiple* methods.
