# SELECCIONA la SUMA (o CUENTA)

¡Nuevo equipo de meta! Mira la entidad `FortuneCookie`. Una de sus propiedades es `$numberPrinted`, que es el número de veces que hemos impreso esa fortuna. En la página de categoría, aquí arriba, quiero imprimir el número total impreso para todas las fortunas de esta categoría.

Podríamos resolver esto haciendo un bucle sobre `$category->getFortuneCookies()`... llamando a`->getNumberPrinted()` y añadiéndolo a alguna variable `$count`. Eso funcionaría siempre que tuviéramos siempre un número pequeño de galletas de la suerte. Pero el negocio de las galletas está en auge... y pronto tendremos cientos de galletas en cada categoría. Sería una enorme ralentización si consultáramos 500 galletas de la suerte sólo para calcular la suma. De hecho, ¡probablemente nos quedaríamos sin memoria antes!

Seguro que hay una forma mejor, ¿verdad? ¡Seguro que sí! Haz todo ese trabajo en la base de datos con una consulta de suma.

## Anular los campos seleccionados

Pensemos: los datos que estamos consultando provendrán en última instancia de la entidad `FortuneCookie`... así que abre `FortuneCookieRepository` para que podamos añadir un nuevo método allí. ¿Qué te parece: `public function countNumberPrintedForCategory(Category $category): int`.

[[[ code('13fef07061') ]]]

La consulta empieza más o menos como todas. Digamos`$result = $this->createQueryBuilder('fortuneCookie')`. Por cierto, el alias puede ser cualquier cosa. Personalmente, intento que sean lo suficientemente largos como para ser únicos en mi proyecto... pero lo suficientemente cortos como para no resultar molestos. Y lo que es más importante, en cuanto elijas un alias para una entidad, quédate con él.

[[[ code('e1b87d3878') ]]]

Vale, sabemos que cuando creamos un QueryBuilder, seleccionará todos los datos de `FortuneCookie`. ¡Pero en este caso, no queremos eso! Así que, a continuación, decimos`->select()` para anular eso.

Antes, en `CategoryRepository`, utilizamos `->addSelect()`, que básicamente dice:

> Coge lo que estemos seleccionando y selecciona también esto otro.

Pero esta vez, estoy utilizando a propósito `->select()` para que anule eso y sólo seleccione lo que pongamos a continuación. Dentro, escribe DQL: `SUM()` una función con la que probablemente estés familiarizado seguida de `fortuneCookie.` y el nombre de la propiedad que queremos utilizar - `numberPrinted`. Y no hace falta que lo hagas, pero voy a añadir `AS fortunesPrinted`, que dará nombre a ese resultado cuando sea devuelto. Lo veremos en un minuto.

[[[ code('b24e158822') ]]]

## andWhere() con una entidad entera

Vale, eso se ocupa del `->select()`. Ahora necesitamos un `->andWhere()` con`fortuneCookie.category = :category`... llamando a `->setParameter()` para rellenar el `category` dinámico con el objeto `$category`.

[[[ code('2eadb05dee') ]]]

¡Esto también es interesante! En SQL, normalmente diríamos algo como`WHERE fortuneCookie.categoryId =` y luego el ID entero. Pero en Doctrine, no pensamos en las tablas ni en las columnas: nos centramos en las entidades. Y no hay ninguna propiedad`categoryId` en `FortuneCookie`. En su lugar, cuando decimos`fortuneCookie.category` estamos haciendo referencia a la propiedad `$category` de`FortuneCookie`. Y en lugar de pasar sólo el ID entero, pasamos el objeto`Category` completo. En realidad es posible pasar el ID, pero la mayoría de las veces pasarás el objeto entero de esta manera.

Bien, ¡terminemos esto! Convierte esto en una consulta con `->getQuery()`. A continuación, si lo piensas bien, en realidad sólo queremos una fila de resultados. Así que digamos`->getOneOrNullResult()`. Por último, `return $result`.

[[[ code('07ac4a22ff') ]]]

Hasta ahora, todas nuestras consultas han devuelto objetos. Puesto que estamos seleccionando sólo una cosa... ¿cambia eso finalmente? ¡Averigüémoslo! Añade `dd($result)` y luego dirígete a `FortuneController` para utilizarlo. Para el controlador de la página mostrar, añade un argumento `FortuneCookieRepository $fortuneCookieRepository`. A continuación, di`$fortunesPrinted` igual a `$fortuneCookieRepository->countNumberPrintedForCategory()`pasando por `$category`.

[[[ code('e07bc76f80') ]]]

¡Estupendo! Toma esa variable `$fortunesPrinted` y pásala a Twig como`fortunesPrinted`.

[[[ code('cf6579e316') ]]]

Por último, busca la plantilla - `showCategory.html.twig` - y... hay un encabezado de tabla que dice "Historial de impresión". Añade unos paréntesis con `{{ fortunesPrinted }}`. Añade `|number_format` para que quede más bonito que la palabra `total`.

[[[ code('8580109145') ]]]

¡Fantástico! Ya que tenemos ese `dd()`, vamos a actualizar y... ¡mira eso! ¡Volvemos a tener un array con 1 clave llamada `fortunesPrinted`! Sí, en cuanto empezamos a seleccionar datos específicos, nos devuelve esos datos específicos. Es exactamente como esperarías con una consulta SQL normal.

Si hubiéramos dicho `->select('fortuneCookie')` (lo cual es redundante porque eso es lo que ya hace`createQueryBuilder()` ), eso nos habría dado un objeto `FortuneCookie`. Pero en cuanto seleccionamos una cosa concreta, se deshace del objeto y devuelve una matriz asociativa.

## Utilizar getSingleScalarResult()

Como nuestro método debe devolver un `int`, podríamos completarlo diciendo`return $result['fortunesPrinted']`. Pero si te encuentras en una situación en la que estás seleccionando una fila de datos... y sólo una columna de datos, hay un atajo para obtener esa única columna: `->getSingleScalarResult()`. Podemos devolverla directamente.

[[[ code('ebd4d16b68') ]]]

Mantendré el `dd()` para que podamos verlo. Y... ¡impresionante! Obtenemos sólo el número! Bueno, técnicamente es una cadena. Si quieres ser estricto, puedes añadir `(int)`. Y ahora... ¡ya está! ¡Tenemos un número total bien formateado!

[[[ code('c11ec1f61d') ]]]

A continuación: Seleccionemos aún más datos y veamos cómo se complican las cosas.
