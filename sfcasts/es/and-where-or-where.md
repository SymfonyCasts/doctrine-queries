# yDónde() y oDónde()

Nuestro sitio tiene un ingenioso cuadro de búsqueda que... no funciona. Si pulso "enter" para buscar "almuerzo", añade `?q=lunch` al final de la URL... pero los resultados no cambian. ¡Vamos a conectar esto!

## Agarrar el parámetro de consulta de búsqueda

Gira y encuentra nuestro controlador: `FortuneController`. Para leer el parámetro de consulta, necesitamos el objeto `Request` de Symfony. Añade un nuevo argumento -no importa si es el primero o el último-, escribe `Request` -el de Symfony-, pulsa "tab" para añadir esa declaración `use`, y di `$request`. Podemos poner el término de búsqueda aquí abajo con `$searchTerm = $request->query->get('q')`.

[[[ code('7dd250d94f') ]]]

Estamos utilizando `q`... sólo porque es lo que elegí en mi plantilla... puedes verlo aquí abajo en `templates/base.html.twig`. Esto se construye con un formulario muy simple que incluye `<input type="text"`, `name="q"`. Así que estamos leyendo el parámetro de consulta `q` y estableciéndolo en `$searchTerm`.

Debajo, `if` tenemos un `$searchTerm`, establecemos `$categories` en`$categoryRepository->search()` (un método que vamos a crear) y pasamos`$searchTerm`. Si no tenemos un `$searchTerm`, reutiliza la lógica de consulta que teníamos antes.

[[[ code('ac8b4116f6') ]]]

## Añadir una cláusula WHERE

¡Estupendo! ¡Vamos a crear ese método `search()`!

En nuestro repositorio, digamos `public function search()`. Tomará un argumento `string
$term` y devolverá un `array`. Como la última vez, añadiré un PHPDoc que diga que devuelve un array de objetos `Category[]`. Elimina el `@param`... porque eso no añade nada.

[[[ code('00e5e3be99') ]]]

Vale: nuestra consulta empezará como antes... aunque podemos ponernos más sofisticados y `return`inmediatamente. Di `$this->createQueryBuilder()` y utiliza el mismo alias `category`. Es una buena idea utilizar siempre el mismo alias para una entidad: nos ayudará más adelante a reutilizar partes de un constructor de consultas.

[[[ code('8b322fc1c0') ]]]

Para la cláusula `WHERE`, utiliza `->andWhere()`. También existe un método `where()`... ¡pero creo que nunca lo he utilizado! Y... tú tampoco deberías. Utilizar `andWhere()`siempre está bien, aunque sea la primera cláusula `WHERE`... y en realidad no necesitamos la parte "y". Doctrine es lo suficientemente inteligente como para darse cuenta.

## andWhere() vs where()

¿Qué tiene de malo `->where()`? Bueno, si antes has añadido una cláusula `WHERE` a tu `QueryBuilder`, llamar a `->where()` eliminaría eso y lo sustituiría por lo nuevo... que probablemente no es lo que quieres. `->andWhere()` siempre se añade a la consulta.

Dentro di `category`, y como quiero buscar en la propiedad `name` de la entidad`Category`, di `category.name =`. La siguiente parte es muy importante. Nunca, nunca, nunca añadas la parte dinámica directamente a tu cadena de consulta. Esto te expone a ataques de inyección SQL. Vaya. En lugar de eso, cada vez que necesites poner una parte dinámica en una consulta, pon en su lugar un marcador de posición: como `:searchTerm`. La palabra `searchTerm`podría ser cualquier cosa... y tú la rellenas diciendo`->setParameter('searchTerm', $term)`.

[[[ code('b36f6e5355') ]]]

¡Perfecto! El final es fácil: `->getQuery()` para convertir eso en un objeto `Query` y luego `->getResult()` para ejecutar esa consulta y devolver la matriz de objetos `Category`.

[[[ code('3e388c01c6') ]]]

¡Estupendo! Si nos dirigimos y probamos esto... ¡ya lo tengo!

## Hacer la consulta difusa

Pero si quitamos algunas letras y volvemos a buscar... ¡no obtenemos nada! Lo ideal es que la búsqueda sea difusa: que coincida con cualquier parte del nombre.

Y eso es fácil de hacer. Cambia nuestro `->andWhere()` de `=` a `LIKE`... y aquí abajo, por `searchTerm`... esto parece un poco raro, pero añade un porcentaje antes y después para hacerlo difuso en ambos lados.

[[[ code('cc256f265b') ]]]

Si lo probamos ahora... ¡eureka!

## Cuidado con orWhere

¡Pero pongámonos más duros! Cada categoría tiene su propio icono - como `fa-quote-left` o el que tiene debajo `fa-utensils`. ¡Esto también es una cadena que se almacena en la base de datos!

¿Podríamos hacer que nuestra búsqueda también buscara en esa propiedad? ¡Por supuesto! Sólo tenemos que añadir un `OR` a nuestra consulta.

Aquí abajo, podrías tener la tentación de utilizar este bonito `->orWhere()` pasando a `category.`con el nombre de esa propiedad... que... si miramos en `Category` rápidamente... es `$iconKey`. Así que `category.iconKey LIKE :searchTerm`.

Y sí, podríamos hacerlo. Pero ¡no lo hagas! Recomiendo no utilizar nunca `orWhere()`. ¿Por qué? Porque... las cosas se pueden poner raras. Imagina que tuviéramos una consulta como ésta: `->andWhere('category.name LIKE :searchTerm')`, `->orWhere('category.iconKey LIKE :searchTerm')` `->andWhere('category.active = true')` .

¿Ves el problema? Lo que probablemente estoy intentando hacer es buscar categorías... pero sólo todas las que coincidan con categorías activas. En realidad, si el `searchTerm`coincide con `iconKey`, se devolverá un `Category`, esté activo o no. Si escribiéramos esto en SQL, incluiríamos paréntesis alrededor de las dos primeras partes para que se comportara. Pero cuando utilizas `->orWhere()`, eso no ocurre.

Entonces, ¿cuál es la solución? Utiliza siempre `andWhere()`... y si necesitas un `OR`, ¡ponlo justo dentro! Sí, lo que pasas a `andWhere()` es DQL, así que podemos decir`OR category.iconKey LIKE :searchTerm`.

[[[ code('3ef066fb97') ]]]

¡Y ya está! En el SQL final, Doctrine pondrá paréntesis alrededor de este `WHERE`.

¡Vamos a probarlo! Gira e intenta buscar "utensilios". Escribo parte de la palabra y... ¡ya está! ¡Coincidimos en el `iconKey`!

Ah, y para mantener la coherencia con la página de inicio normal, incluyamos`->addOrderBy('category.name', 'DESC')`.

[[[ code('f49e79613c') ]]]

Ahora, si vamos a la página de inicio y escribimos la letra "p" en la barra de búsqueda, ¡sí! se ordena alfabéticamente.

Y si tienes dudas sobre tu consulta, siempre puedes ir al perfilador de Doctrine para ver la versión formateada. Es exactamente lo que esperábamos.

A continuación: Vamos a ampliar nuestra consulta, para que podamos buscar en las galletas de la suerte que hay dentro de cada categoría. Para ello, necesitaremos un `JOIN`.