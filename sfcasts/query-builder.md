# The QueryBuilder

For me, it's really powerful to understand that DQL is ultimately what's happening behind the scenes in Doctrine. But most of the time we're not going to build this DQL string by hand. We're gonna use something called the query builder. So I'm gonna comment out my DQL and we're gonna rebuild that with the query builder. So I'll say `qb` for query builder equals `$this->createQueryBuilder('category')`. 

So because we're inside of the `CategoryRepository`, when we say `createQueryBuilder()`, that kind of automatically adds the `from App\Entity\Category` for us. And then what we're passing here as `'category'` is the alias. And also by default, it's going to select everything. So just by saying this, we've actually already created most of this query. 

To add the next spot, you can actually chain off of this and say `addOrderBy()`. And here we're saying `'category.name'`. And then we can use this `Criteria` object, I'll hit tab to autocomplete that. `Criteria::DESC`. Or you could just put the string `'DESC'`, it's the same thing. 

Now this next line, we still need that `Query` object, but this time we can get it just by saying `qb->getQuery()`. So in theory, this will give us the exact same DQL as before. And actually we can prove this by saying `dd($query->getDQL())` and see what it's going to translate this in as DQL. When we try that, yeah, that is exactly what we created a second ago. It's the exact same string. So no surprise if we remove that `dd()` and refresh, we're back to working. It's just that easy. 

Alright, so this is some nice, the query builder basics are here and this is nice. Let's get more complex by adding `andWhere()` and `orWhere()` next.
