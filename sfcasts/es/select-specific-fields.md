# Seleccionar campos específicos

¡Añadamos más cosas a esta página! ¿Qué tal el número medio de galletas de la suerte impresas para esta categoría? Para ello, vuelve a nuestra consulta: vive en `countNumberPrintedForCategory()`.

[[[ code('9624cb857e') ]]]

## Seleccionar el promedio

Para obtener la media, podemos añadir una coma y utilizar la función `AVG()`. O podemos utilizar `addSelect()`... que me parece un poco mejor. Queremos el `AVG()` de`fortuneCookie.numberPrinted` aliasado a `fortunesAverage`.

Esta vez no he utilizado la palabra `AS`... sólo para demostrar que la palabra`AS` es opcional. De hecho, toda la parte `fortunesAverage` o `AS fortunesPrinted`es opcional. Pero dándole un nombre a cada una, podemos controlar las claves de la matriz de resultados final, que veremos en un minuto.

[[[ code('d34b7c739c') ]]]

Ya que estamos aquí, en lugar de imprimir el nombre del objeto `$category`, veamos si podemos obtener el nombre de la categoría dentro de esta consulta. Diré`->addSelect('category.name')`.

Si ves un problema con esto, ¡tienes razón! Pero ignorémoslo y avancemos a ciegas! `dd($result)` al final.

[[[ code('7bb36423bf') ]]]

Antes, esto sólo devolvía el entero `fortunesPrinted`. Pero ahora, estamos seleccionando tres cosas. ¿Qué nos devolverá ahora?

La respuesta es... ¡un error gigantesco!

> 'categoría' no está definida.

Sí, he hecho referencia a `category`... pero nunca nos hemos unido a ella. Añadámoslo. Estamos consultando desde la entidad `FortuneCookie`, y ésta tiene una propiedad `category`, que es un `ManyToOne`. Así que nos estamos uniendo a un objeto. Hazlo con `->innerJoin()` pasando a `fortuneCookie.category` y dándole el alias`category`.

[[[ code('e96e24e7db') ]]]

## Devolución de varias columnas de resultados

Si ahora vamos a actualizar la página... éste es el error que esperaba:

> La consulta devolvió una fila que contenía varias columnas.

Este `->getSingleScalarResult()` es perfecto cuando devuelves una sola fila y una sola columna. En cuanto devuelvas varias columnas,`->getSingleScalarResult()` no funcionará. Para solucionarlo, cambia a `->getSingleResult()`.

[[[ code('232f2e4df8') ]]]

Esto básicamente dice

> Dame la única fila de datos de la base de datos.

Inténtalo de nuevo. ¡Eso es lo que queremos! ¡Devuelve exactamente las tres columnas que hemos seleccionado!

Y ahora... tenemos que cambiar un poco este método. Actualiza el retorno `int` a un `array`... y, aquí abajo, quita el `(int)` por completo y devuelve `$result`. También podemos quitar el `dd()`... y podrías poner el `return` aquí arriba si quisieras.

[[[ code('96efdd07dc') ]]]

## Actualizar nuestro proyecto para utilizar los resultados

¡Nuestro método está listo! Ahora vamos a arreglar el controlador. Este`$fortunesPrinted` ya no está bien. Cámbialo por `$result` en su lugar. Luego... léelo abajo con - `$result['fortunesPrinted']`. Copia eso, pégalo, y envía una variable `fortunesAverage` a la plantilla ajustada a la clave `fortunesAverage`. Pasa también `categoryName` ajustada a `$result['name']`.

[[[ code('7a72879e26') ]]]

¡Hora de la plantilla! En `showCategory.html.twig`, tenemos acceso a todo el objeto`$category`... que es como estamos imprimiendo `category.name`. Pero ahora, también tenemos una variable `categoryName`. Sustituye `category.name` por `categoryName`.

[[[ code('92649e7461') ]]]

No hay... ninguna razón real para hacerlo: sólo estoy demostrando que podemos obtener datos adicionales en nuestra nueva consulta. Aunque, si también hubiéramos seleccionado `iconKey`, entonces podríamos evitar por completo la consulta del objeto `Category`. Sin embargo, aunque eso podría hacer que nuestra página fuera un poco más rápida, es casi definitivamente una exageración y hace que nuestro código sea más confuso. ¡Utilizar objetos es lo mejor!

Vale, a continuación, para el "Historial de impresión", pulsa "enter" y añade`{{ fortunesAverage|number_format }}` y luego `average`.

[[[ code('b8b2aec6eb') ]]]

Fantástico. ¡Inténtalo de nuevo! Si no me he equivocado... ¡ya está! ¡Todo funciona! Tenemos dos consultas: una para el `category` que está unido a`fortune_cookies` y la que acabamos de hacer que coge el `SUM`, `AVG`, y el `name` también con un `JOIN`. ¡Me encanta!

Recibir objetos de entidad completos de Doctrine es la situación ideal porque... es muy agradable trabajar con objetos. Pero al fin y al cabo, si necesitas consultar datos o columnas específicos, puedes hacerlo perfectamente. Y como acabamos de ver, Doctrine devolverá una matriz asociativa.

Sin embargo, podemos ir un paso más allá y pedirle a Doctrine que nos devuelva esos datos concretos dentro de un objeto. Hablemos de ello a continuación.
