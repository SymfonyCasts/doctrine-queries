# Relaciones EXTRA_LAZY

Vuelve a la página de inicio sin consulta de búsqueda. Todavía tenemos siete consultas porque seguimos utilizando nuestro método muy simple `findAllOrdered()`... que no tiene el `JOIN`. Así que... deberíamos añadir el `JOIN` aquí también, ¿no? Sí, bueno... probablemente. Pero quiero mostrarte una solución alternativa.

Nuestra página de inicio es única porque en realidad no necesitamos todos los datos de `FortuneCookie` para cada `Category`... lo único que necesitamos es el `COUNT`.

Fíjate en la plantilla: no estamos haciendo un bucle sobre `category.fortuneCookies` y mostrando los datos reales de `FortuneCookie`. No, simplemente los estamos contando. Si lo piensas, tener una consulta gigante que coge todos los datos de `FortuneCookie`.... sólo para contarlos... no es lo mejor para la eficiencia.

[[[ code('b9b9f1787f') ]]]

## Añadiendo fetch: EXTRA_LAZY

Si te encuentras en esta situación, puedes decirle a Doctrine que sea inteligente con la forma en que carga la relación. Entra en la entidad `Category` y busca la relación `OneToMany`para `$fortuneCookies`. Al final, añade `fetch:` fijado en `EXTRA_LAZY`.

[[[ code('8b1ff4cbcd') ]]]

Vamos a ver qué hace eso. Cuando actualices, observa el recuento de consultas. ¡Se queda en siete! Pero si abrimos el perfilador, las consultas en sí han cambiado. La primera es la misma: consulta desde `category`. ¡Pero fíjate en las demás! ¡Tenemos `SELECT COUNT(*) FROM fortune_cookie` una y otra vez! Así que tenemos siete consultas, ¡pero ahora cada una sólo selecciona el `COUNT`!

Cuando tienes `fetch: 'EXTRA_LAZY'` y simplemente cuentas una relación de colección, Doctrine es lo suficientemente inteligente como para seleccionar sólo el `COUNT` en lugar de consultar todos los datos. Si hiciéramos un bucle sobre esta colección y empezáramos a imprimir los datos de`FortuneCookie`, seguiría haciendo una consulta completa de los datos. Pero si lo único que necesitamos es contarlos, entonces `fetch: 'EXTRA_LAZY'` es una gran solución.

## Consulta personalizada en la página de mostrar categorías

Vale: haz clic en una de las categorías. El perfilador dice que tenemos dos consultas. Se trata de una especie de problema N+1 en "miniatura". La primera consulta selecciona un único`Category`... y la segunda selecciona todas las galletas de la suerte de esta única categoría. Utilicemos nuestras habilidades en `JOIN` para reducirlo a una sola consulta.

Abre `FortuneController` y busca la acción `showCategory()`. Al escribir`Category` en este argumento, le estamos diciendo a Symfony que busque `Category`por nosotros, utilizando `{id}`. Normalmente, ¡esto me encanta! Sin embargo, en este caso, como queremos añadir un `JOIN` de `Category` a `fortuneCookies`, tenemos que tomar el control de esa consulta.

[[[ code('41d5630275') ]]]

Cambia esto para que Symfony nos pase el `int $id` directamente. Luego, autocablea`CategoryRepository $categoryRepository`.

[[[ code('ed4e3086f2') ]]]

A continuación, haz la consulta manualmente con `$category = $categoryRepository->`... llamando a un nuevo método: `findWithFortunesJoin($id)`. Antes de crearlo, también tenemos que añadir `if (!$category)`, y luego `throw $this->createNotFoundException()`. Si quieres, puedes darle un mensaje.

Vale, copia el nombre del método, salta a `CategoryRepository` y di`public function findWithFortunesJoin(int $id)`, que devolverá un `Category`si se encuentra alguno, si no `null`. Arreglaré la errata en un momento.

[[[ code('0fbc284bba') ]]]

La consulta empieza como la otra.... y podríamos robar algo de código... pero como estamos practicando, vamos a escribirla a mano. `return $this->createQueryBuilder()` y pasar nuestro alias normal `category`. Luego `->andWhere('category.id = :id')` -también arreglaré esa errata en un minuto- rellenando el comodín con `->setParameter()``id` , `$id`... idealmente escrito correctamente. Luego `->getQuery()`.

[[[ code('1e0d314c7b') ]]]

Hasta ahora, hemos estado buscando varias filas... y por eso hemos utilizado `->getResult()`. Pero esta vez, queremos un único resultado o null si no se puede encontrar. Para ello, utiliza `->getOneOrNullResult()`.

[[[ code('f0cb11c346') ]]]

Y ya está Con esto deberían funcionar las cosas. Haré una pequeña comprobación de cordura por aquí, y... oh... probablemente ayudaría si escribiera las cosas correctamente. ¡Pero esto es genial! Ha reconocido que no sabía qué era ese alias y nos ha dado un error claro. Y ahora... funciona, y seguimos teniendo dos consultas.

## Añadir una unión

¡Ha llegado la hora de `JOIN`! Vamos a pasar de una `Category` a muchas galletas de la fortuna, así que digamos `->leftJoin()` sobre `category.` y el nombre de la propiedad, que es`fortuneCookies`. Una vez más, el orden no importa, pero arriba diré`->addSelect('fortuneCookie')`. Ah, y también tengo que añadir `fortuneCookie` como segundo argumento dentro de `->leftJoin()`: ése es el alias.

[[[ code('07444b3b1a') ]]]

Así que estamos poniendo el alias de esa entidad unida en `fortuneCookie` y luego seleccionando `fortuneCookie`. Ahora, deberíamos ver que el número de esta consulta pasa de dos a uno. Y... ¡así ha sido!

Éstas son las conclusiones: aunque no hay necesidad de sobreoptimizar, si tienes el problema N+1, puedes resolverlo uniéndote a la tabla relacionada y seleccionando sus datos.

Vale, hasta ahora Doctrine devolvía una colección de objetos `Category` o un único objeto`Category`. Eso está muy bien, pero ¿y si, en lugar de objetos enteros, sólo necesitamos algunos datos, como unas cuantas columnas, un `COUNT`, o un `SUM`? Vamos a profundizar en ello a continuación.
