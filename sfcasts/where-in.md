# WHERE IN ()

We have categories for "Pets" and "Love", but if we search up here for "pets love", there are no results. That makes sense. We're searching to see if this string is matching the `name` or the `iconKey`. So let's make our search feature a little more complicated to see if we can match both of those categories, searching word by word.

The query for this is over in `/Repository/CategoryRepository.php`. Here's our `search()`, and `$term` is the string we typed in. Down here, let's say `$termList = explode()`. I'll leave a space, and then say `$term`. If you want a really rich search, you should actually use a real search provider, but we can do some pretty cool stuff in here to get a nice search just using our database.

This is going to be an array of all of the words inside the search term. I want this to be a `WHERE IN`, so it basically reads `->andWhere()`, where `category.name` is *in* one of these terms, so if the category name is "Love" and "Love" *is* one of these terms, it will match. We can *totally* do that!

We already have `category.name LIKE :searchTerm`, and right after it, I'm going to add `OR category.name IN` and the nest part is a bit of tricky syntax. We want to use `()` here. If we were writing a SQL query, we would normally do something like this. In our case, we're not going to put the string inside the query. Instead, we're going to create a new parameter. How about... `:termList`. Then, down here, we need to pass this in, so let's say `->setParameter('termList', $termList)`. The key thing here is, when you use `IN`, you're going to need the parentheses like normal. But what you're *actually* passing as the parameter is an *array*. It's not a comma-separated list. There's no need to do that. By simply passing the array, Doctrine will figure it out. And now... nice! It's *just* that easy.

Next: You're probably familiar with the `RAND()` function for MySQL, or maybe the `YEAR()` function, or one of the many MySQL or PostgreSQL functions that exist. Well, you might be surprised to learn that some of those *won't* work.
