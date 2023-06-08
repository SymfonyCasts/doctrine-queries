# DONDE EN()

Tenemos categorías para "Mascotas" y "Amor", pero si buscamos aquí arriba "mascotas amor"... ¡no hay resultados! Eso tiene sentido. Estamos buscando si esta cadena coincide con`name` o con `iconKey`. Hagamos nuestra búsqueda más inteligente para ver si podemos coincidir con ambas categorías buscando palabra por palabra.

La consulta para esto vive en `CategoryRepository`... en el método `search()`. El argumento`$term` es la cadena que escribimos. Aquí abajo, digamos que`$termList =` luego `explode` esa cadena en una matriz dividiéndola en espacios vacíos. Si quieres una búsqueda realmente rica, deberías utilizar un sistema de búsqueda real. Pero podemos hacer cosas bastante chulas sólo con la base de datos.

[[[ code('38a4d57d5f') ]]]

Éste es el objetivo: quiero que también coincidan los resultados en los que `category.name` esté en una de las palabras de la matriz.

## Utilizando la IN

Justo después de `category.name LIKE :searchTerm`, añade `OR category.name IN`. Lo único complicado de esto es la sintaxis. Añade `()`. Si estuviéramos escribiendo una consulta SQL sin formato, escribiríamos aquí una lista, como `'foo', 'bar'`. Pero con el constructor de consultas, en lugar de eso, pon un marcador de posición, como `:termList`. A continuación pásalo:`->setParameter('termList', $termList)`.

[[[ code('e173b7be76') ]]]

La clave es que, cuando utilices `IN`, necesitarás los paréntesis como siempre... pero dentro de eso, en lugar de una lista separada por comas, pondrás un array. Doctrine transformará eso por nosotros.

Y ahora... ¡bien! Una vez que sabes cómo funciona, es así de fácil.

Lo siguiente: Probablemente estés familiarizado con la función `RAND()` para MySQL, o quizá con la función`YEAR()`... o con alguna de las muchas funciones MySQL o PostgreSQL que existen. Pues bien, quizá te sorprenda saber que algunas de ellas no funcionan de forma inmediata.
