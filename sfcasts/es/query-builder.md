# El QueryBuilder

Es realmente poderoso comprender que DQL es, en última instancia, lo que se utiliza entre bastidores en Doctrine. Pero la mayoría de las veces, no vamos a construir esta cadena DQL a mano. No, vamos a utilizar algo llamado "QueryBuilder". Ooooh.

## Crear el QueryBuilder

Comenta el DQL. Vamos a reconstruirlo con el QueryBuilder. Empieza con `$qb`(por "QueryBuilder") `= $this->createQueryBuilder()`. Dentro, di `category`.

[[[ code('3f81afc309') ]]]

Como estamos dentro de `CategoryRepository`, cuando decimos `createQueryBuilder()`, eso añade automáticamente `FROM App\Entity\Category` y lo aliasa a `category`, ya que eso es lo que pasamos como argumento. Esto también selecciona todo por defecto. Así que... ¡con sólo esto, ya hemos recreado la mayor parte de esta consulta!

Para añadir el siguiente punto, puedes encadenar esto: `->addOrderBy()` con`category.name`. Luego usaré esta clase `Criteria` ( pulsa "tab" para autocompletarlo) seguida de `DESC`. O puedes poner simplemente la cadena `'DESC'`: es lo mismo.

[[[ code('5ce5c06ca1') ]]]

## Ejecutar el QueryBuilder

¡QueryBuilder listo! Para ejecutarlo, seguimos necesitando ese objeto `Query`. Ahora podemos obtenerlo con `$qb->getQuery()`. Internamente, esto debería generar exactamente el mismo DQL que antes, ¡y puedo probarlo! Añade un `dd()` con `$query` y, en lugar de decir `->getSQL()`, di `->getDQL()`.

[[[ code('be01d0ea19') ]]]

Cuando probemos eso... ¡sí! ¡Es exactamente lo que escribimos antes! Así que, sin sorpresas, si eliminamos ese `dd()` y actualizamos... ¡volvemos a funcionar! Así de fácil.

[[[ code('69dcc4b9c9') ]]]

Vale, ya tenemos lo básico del QueryBuilder. Pongámonos más complejos añadiendo a continuación`andWhere()` y `orWhere()`.
