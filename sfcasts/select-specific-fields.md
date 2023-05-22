# Selecting Specific Fields

Having a bunch of useful information all in one place is *nice*, so let's add more to our page. How about the average number of fortune cookies that have been printed in this category? To do that, we need to head back to our query. It's called `countNumberPrintedForCategory()`, but we're going to tweak it a little. We *could* add a comma here and then use the `AVG()` function to select more. *Or* we can just use `addSelect()` and just move that onto two lines. Let's go with this, but inside, we'll add the `AVG()` function with `fortuneCookie.numberPrinted`, and then `fortunesAverage`. you may have noticed that I didn't use the word `AS` here. I'm just doing this to demonstrate that this is totally optional. In fact, this *entire* thing is optional. We could just leave this off, but by giving it a name, it gives me control over which key will be returned in the array, which we'll see in a second.

While we're here, we can grab whatever data we want. Maybe, instead of printing out this name from the `$category` object, let's see if we can grab the category name right here. I'll say `->addSelect('category.name')`. You *probably* see a problem with that, and you'd be correct. Let's ignore that for a second just to see what this gives us. I'll add `dd($result)`. Before, this was just returning the `fortunesPrinted`, but *now*, we're selecting *three* things, not just one. So what's it going to return this time? The answer is... a gigantic error. It says:

`Error: 'category' is not defined.`

If you were watching closely, you probably noticed that we're referencing `category`, but we haven't *joined* over to that. Remember, we're querying from the `fortuneCookie` entity and it has a `$category` property. We're going to `JOIN` from the one-or-many fortune cookies to one `$category`. In this case, we want an `->innerJoin()`. I'll say `->innerJoin('fortuneCookie.category', 'category')`.

If we go refresh the page now... *this* is the error I was expecting:

`The query returned a row containing multiple columns.`

This `->getSingleScalarResult()` is perfect when you're just returning a single row and a single column. As soon as you're returning *multiple* columns, `->getSingleScalarResult()` is *not* going to work. To fix that, we're going to change this to `->getSingleResult()`. This is basically saying:

`Give me the one row of data that's going to come back.`

If we try this one more time... that's what we expect! This gave us the exact three colums we were selecting. Sweet! Now let's change this method a little bit. We'll change this `int` to an `array` and, down here, we'll remove this `(int)` entirely so this just returns `$result`. We can also remove our `dd`. And you could just put the `return` up here if you wanted to.

Our method is good to go, so now, let's go back and fix our controller. This `$fortunesPrinted` is not quite right. Let's change that to `$result` instead. We'll add it again down here with some array keys - `$result['fortunesPrinted']` - and then we can copy and paste that below and change this to `$result['fortunesAverage']`. Finally, say `categoryName => $result[]` (we didn't really need a query for that, but we got it) with `name` inside.

Over in our `showCategory.html.twig` template, if you'll remember, we're passing the *entire* `$category` object in, which is how we're printing `category.name`. But *now*, we're also passing in a `categoryName` *variable*, so I'm going to replace this `category.name` with `categoryName`. There's no actual reason for me to do that, but if I had also selected `iconKey`, then we wouldn't even *need* the `categoryName` variable anymore. If that were the case, we could change this first query here to one that just queries for the fortune cookies and not the category data, since we'll have grabbed the category data in this *other* query. That's just a little optimization, but don't worry about that too much. Since I *did* query for the `categoryName` here, I just wanted you to see that we're going to be able to use that *directly*.

Down here, for the "Print History", hit "enter" and then we'll say `{{ fortunesAverage|number_format }}` and `average`. *Awesome*. We'll try this again, and if I didn't make any mistakes... got it! Everything *works*, and we've got our two queries here: The one for the `category` that's joined over to our fortune cookies, and the one that we just made, where we're garbbing the `SUM`, `AVG`, and the `name` with our `JOIN`. Love it!

Getting objects back from Doctrine is the most ideal situation because objects are just really nice to work with. But at the end of the day, if you ever need to query for certain random data or fields, you can *totally* do that, and Doctrine is going to return a very simple associative array. *But* we can go one step further with that and ask Doctrine to return this random data *inside* an object. Let's talk about that *next*.
