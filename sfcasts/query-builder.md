# The QueryBuilder

It's really powerful to understand that DQL is *ultimately* what's happening behind the scenes in Doctrine. But most of the time, we're not going to build this DQL string by hand. We're going to use something called the "QueryBuilder". I'll comment out my DQL and we're going to *rebuild* that with the QueryBuilder. Say `$qb` (for "QueryBuilder") `= $this->createQueryBuilder()`. Inside, say `category`.

Since we're inside `CategoryRepository.php`, when we say `createQueryBuilder()`, that will automatically add `FROM App\Entity\Category`. We're also passing `category` as the alias. This will select *everything* by default, so with *just* this, we've already created *most* of this query.

To add the next spot, you can actually *chain* off of this and say `addOrderBy()` with `category.name`. *Then* we can use this `Criteria::` object (I'll hit "tab" to autocomplete that) followed by `DESC`. *Or* you could just put the string `'DESC'`. It's the same thing. 

For the next line, we'll still need that `$query` object, but *this* time, we can just get it by saying `$qb->getQuery()`. *In theory*, this will give us the exact same DQL as before, but we can prove it! Add a `dd()` with `$query` and, instead of saying `->getSQL()`, we'll say `->getDQL()` so we can see how this will translate as DQL. When we try that... yeah! That's exactly what we created a second ago! It's the *exact* same string. So, no surprise, if we remove that `dd()` and refresh... we're back to working! It's just that easy. 

Okay, we have some QueryBuilder basics here. This is nice! Let's get more complex by adding `andWhere()` and `orWhere()` *next*.
