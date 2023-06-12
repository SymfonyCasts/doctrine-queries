# Utilizar RAND() u otras funciones no compatibles

Por si acaso, vamos a aleatorizar el orden de las fortunas de una página. 
Prueba con esta categoría, que tiene 4.

Empieza abriendo `FortuneController` y buscando `showCategory()`. En este momento, consultamos la categoría de la forma normal. Luego, en nuestra plantilla, hacemos un bucle sobre `category.fortuneCookies`.

Cambia la consulta a `->findWithFortunesJoin()`, que está aquí en`CategoryRepository`. Recuerda: esto se une a `FortuneCookie` y selecciona esos datos, resolviendo nuestro problema N+1.

[[[ code('41d5951e47') ]]]

Ahora que hacemos esto, también podemos controlar el orden. Digamos`->orderBy('RAND()', Criteria::ASC)`. Sólo estamos consultando un `Category`... pero esto controlará también el orden de las galletas de la suerte relacionadas... que veremos cuando hagamos un bucle sobre ellas.

[[[ code('724ce25bbf') ]]]

¡Genial! Si probamos esto... ¿error?

> Esperaba una función conocida, obtuvo `RAND`

Espera... `RAND` es una función conocida de MySQL. Entonces... ¿por qué no funciona? Vale, Doctrine soporta muchas funciones dentro de DQL, pero no todo. ¿Por qué? Porque Doctrine está diseñado para trabajar con muchos tipos diferentes de bases de datos... y si sólo una o algunas bases de datos soportan una función como `RAND`, entonces Doctrine no puede soportarla. Afortunadamente, podemos añadir esta función o cualquier función personalizada que queramos nosotros mismos o, en realidad, a través de una biblioteca.

Busca la biblioteca `beberlei/doctrineextensions`. Es genial. Nos permite añadir un montón de funciones diferentes a varios tipos de bases de datos. Baja aquí y coge la línea `composer require`... pero no necesitamos la parte `dev-master`. ¡Ejecuta eso!

```terminal-silent
composer require beberlei/doctrineextensions
```

Instalar esto no cambia nada en nuestra aplicación... sólo añade un montón de código que podemos activar para las funciones que queramos. Para ello, vuelve a`config/packages/doctrine.yaml`, en algún lugar debajo de `orm`, digamos `dql`. Aquí hay un montón de categorías diferentes, sobre las que puedes leer más en la documentación. En nuestro caso, tenemos que añadir `numeric_functions` junto con el nombre de la función, que es `rand`. Pon esto en la clase que permitirá a Doctrine saber qué hacer: `DoctrineExtensions\Query\Mysql\Rand`.

[[[ code('03cff179d1') ]]]

Definitivamente, no tienes que fiarte de mi palabra sobre cómo debe configurarse esto. En la documentación... hay un enlace "config" aquí abajo... y si haces clic en `mysql.yml`, puedes ver que describe todas las cosas diferentes que puedes hacer y cómo activarlas.

Voy a cerrar eso... actualizo, y... ¡ya está! Cada vez que actualizamos, los resultados aparecen en un orden diferente.

Vale, ¡otro equipo de temas! Terminemos con una situación compleja de `groupBy()` en la que seleccionamos algunos objetos y algunos datos adicionales a la vez.
