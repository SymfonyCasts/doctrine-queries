# Utilizar GROUP BY para buscar y contar en una sola consulta

Un último reto. En la página de inicio, tenemos 7 consultas. Una para obtener las categorías... y 6 más para obtener el recuento de galletas de la suerte de cada una de esas 6 categorías.

Tener 7 consultas no es... probablemente un problema... y no deberías preocuparte por optimizar el rendimiento hasta que realmente veas que hay un problema. Pero desafiémonos a convertir esas 7 consultas en una sola.

Pensemos: podríamos consultar todas las categorías, `JOIN` hasta las galletas de la suerte relacionadas, `GROUP BY` la categoría y, a continuación, `COUNT` las galletas de la suerte. Si eso no tiene sentido, no te preocupes. Lo veremos en acción.

## Utilizar un Grupo Por Para Seleccionar un Objeto + Otros Datos

Dirígete a `FortuneController`. Estamos en la página principal, y estamos utilizando el método`findAllOrdered()` de `$categoryRepository`. Ve a buscar ese método... aquí está. Ya estamos seleccionando desde `category`. Ahora también`->addSelect('COUNT(fortuneCookie.id) AS fortuneCookiesTotal')`. Para unirnos y conseguir ese alias `fortuneCookie`, añade `->leftJoin('category.fortuneCookies')`, luego`fortuneCookie`. Por último, para que este `COUNT` funcione correctamente, di`->addGroupBy('category.id')`.

[[[ code('a7b2c44938') ]]]

Bien, ¡veamos qué obtenemos! Aquí abajo, `dd($query->getResult())`.

[[[ code('513a83c557') ]]]

Antes, esto devolvía un `array` de objetos `Category`. Si refrescamos... es una matriz, pero ahora es una matriz de matrices donde la clave `0` es el objeto `Category`, y luego tenemos este `fortuneCookiesTotal` extra . Así que... ¡seleccionó exactamente lo que queríamos! Pero... cambió la estructura subyacente. Y tenía que hacerlo, ¿no? Tenía que darnos de alguna manera el objeto `Category` y la columna extra entre bastidores.

Elimina la sentencia `dd`. Esto sigue devolviendo un `array`... pero elimina el`@return` porque ya no devuelve una matriz de objetos `Category`. También podríamos actualizarlo a un phpdoc más elegante que describa la nueva estructura.

A continuación, para tener en cuenta el nuevo retorno, dirígete a `homepage.html.twig`. Estamos haciendo un bucle sobre `category in categories`... que ahora no es del todo correcto: la categoría está en este índice `0`. Cámbialo por `for categoryData in categories`... y dentro añade `set category = categoryData[0]`. Es feo, pero hablaremos de ello más adelante.

[[[ code('8012330388') ]]]

Desplázate hasta `length`. En lugar de buscar a través de la relación -lo que funcionaría, pero provocaría consultas adicionales- utiliza`categoryData.fortuneCookiesTotal`.

[[[ code('a5929333b3') ]]]

Hagamos esto Actualiza y... ¡sólo una consulta! ¡Guau!

## La fea estructura de datos

Lo peor de esto es que la estructura de nuestros datos ha cambiado... y ahora tenemos que leer esta fea clave `0`. No lo haré ahora, pero una solución mejor sería aprovechar un objeto DTO para contener esto. Por ejemplo, podríamos crear una nueva clase llamada `CategoryWithFortuneCount` con dos propiedades: `$category` y`$fortuneCount`. En este método del repositorio, podríamos hacer un bucle sobre `$query->getResults()`y crear un objeto `CategoryWithFortuneCount` para cada uno. Al final, nuestro método devolvería una matriz de `CategoryWithFortuneCount`. Devolver una matriz de objetos es mucho mejor que una matriz de matrices... con algún índice aleatorio `0`.

## Arreglar la página de búsqueda

Hablando de esa estructura cambiada, si buscamos algo... obtenemos un error:

> Imposible acceder a una clave "0" en un objeto de la clase `Category`.

Es... esta línea de aquí. Cuando buscamos algo, utilizamos el método `search()`y... ¡sorpresa! Ese método no tiene los nuevos `addSelect()` y`groupBy()`: sigue devolviendo una matriz de objetos `Category`.

[[[ code('e10a64df1c') ]]]

Para solucionarlo, crea un `private function` aquí abajo que pueda contener el grupo por:`addGroupByCategory(QueryBuilder $qb)` y devolverá un `QueryBuilder`. Ah, y haz que el argumento sea opcional... entonces crea un nuevo constructor de consultas si no tenemos ninguno.

[[[ code('45492e9602') ]]]

Vale, sube y roba la lógica - el `->addSelect()`, `->leftJoin()`, y`->addGroupBy()`. Pégalo aquí abajo. Ah, y `addGroupByCategory()` no es un buen nombre: utiliza `addGroupByCategoryAndCountFortunes()`.

[[[ code('caaa7aba76') ]]]

Fantástico. Arriba, ¡simplifica! Cambia esto por `addGroupByCategoryAndCountFortunes()`... y entonces no necesitaremos los `->addGroupBy()`, `->leftJoin()`, o `->addSelect()`.

[[[ code('ac5bea4917') ]]]

Para asegurarnos de que esa parte funciona, gira y... vuelve a la página de inicio. Eso tiene buena pinta... pero si avanzamos... sigue roto. Abajo en `search()`añade `$qb = $this->addGroupByCategoryAndCountFortunes($qb)`.

[[[ code('47e0b13692') ]]]

Y ahora... otro error:

> `fortuneCookie` ya está definido.

¡Vaya! Pero, sí, tiene sentido. Estamos uniendo en nuestro nuevo método... y también en`addFortuneCookieJoinAndSelect()`. Afortunadamente, ya no necesitamos esta segunda llamada: estábamos uniendo y seleccionando para resolver el problema N+1... pero ahora tenemos una consulta aún más avanzada para hacerlo. Copia nuestro nuevo método, bórralo y pégalo sobre el antiguo.

[[[ code('3dbcd5555f') ]]]

Y ahora... ¡ya está! ¡Sólo 1 consulta!

Amigos, ¡lo hemos conseguido! ¡Guau! Gracias por acompañarme en este viaje mágico a través de todas las cosas de Doctrine Query. Estas cosas son raras, geniales y divertidas. Espero que lo hayas disfrutado tanto como yo. Si te encuentras con alguna situación loca en la que no hayamos pensado, tienes alguna pregunta o fotos de tu gato, siempre estamos aquí para ti en los comentarios. Bueno, ¡hasta la próxima!
