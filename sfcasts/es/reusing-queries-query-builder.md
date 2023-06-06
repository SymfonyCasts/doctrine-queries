# Reutilizar consultas en el Generador de consultas

Abre `CategoryRepository`. Aquí tenemos unos cuantos lugares en los que `->leftJoin()`nos lleva a `fortuneCookies` y seleccionamos galletas de la suerte. En el futuro, puede que necesitemos hacer eso en más métodos aún... así que sería súper estupendo si pudiéramos reutilizar esa lógica en lugar de repetirla una y otra vez. ¡Hagámoslo!

[[[ code('bcf4a27d9d') ]]]

En cualquier lugar de aquí dentro, añade un nuevo `private function` llamado`addFortuneCookieJoinAndSelect()`. Éste aceptará un objeto `QueryBuilder` (asegúrate de que obtienes el de `Doctrine\ORM` - el "Object Relational Mapper"), y llamémoslo `$qb`. Esto también devolverá un `QueryBuilder`.

[[[ code('a17214939b') ]]]

El siguiente paso es bastante sencillo. Ve a robar la lógica `JOIN` de arriba... y, aquí abajo, di `return $qb`... y pégalo... asegurándote de limpiar cualquier desorden de espaciado que se haya podido producir.

[[[ code('7bbb6b9429') ]]]

Y... ¡listo! Ahora podemos llamar a este método, pasarle el `QueryBuilder`, y añadirá el `JOIN` y el `SELECT` por nosotros.

El resultado es bastante bonito. Aquí arriba, podemos decir`$qb = $this->createQueryBuilder('category')`... y abajo,`return $this->addFortuneCookieJoinAndSelect()` pasando `$qb`.

[[[ code('6de2fd2c63') ]]]

Creamos el `$qb`, se lo pasamos al método, lo modifica... y también nos devuelve el `QueryBuilder`, así que podemos encadenarlo como siempre.

Gira y prueba la función "Buscar". Y... oh... ¡claro que se rompe! Tenemos que eliminar este exceso de código. Si lo probamos ahora... ¡gran éxito!

Para celebrarlo, repite lo mismo aquí abajo. Sustituye `return` por`$qb =`... más abajo, di `return $this->addFortuneCookieJoinAndSelect()`pasando por `$qb`, y luego elimina `->addSelect()` y `->leftJoin()`.

[[[ code('df154235c0') ]]]

Esto es para la página de Categorías, así que si hacemos clic en cualquier categoría... ¡perfecto! Sigue funcionando.

## Hacer que el argumento QueryBuilder sea opcional

Pero... ¡podemos hacerlo aún mejor! En lugar de pedir el objeto `QueryBuilder`como argumento, hazlo opcional.

Mira: aquí abajo, retoca esto para que si tenemos un `$qb`, lo utilicemos, si no,`$this->createQueryBuilder('category')`. Así que si se ha pasado un `QueryBuilder`, úsalo y llama a `->addSelect()`, si no, crea un nuevo `QueryBuilder` y llama a`->addSelect()` con él.

[[[ code('aec316b433') ]]]

La ventaja es que aquí no necesitamos inicializar en absoluto nuestro `QueryBuilder`... y lo mismo ocurre con el método anterior.

[[[ code('18ce4274ad') ]]]

Pero puedes ver lo importante que es que utilicemos un alias coherente en todas partes. Estamos haciendo referencia a `category.name`,`category.iconKey`, y `category.id`... así que tenemos que asegurarnos de que siempre creamos un `QueryBuilder` utilizando ese alias exacto. De lo contrario... las cosas explotarían.

Añadamos un método reutilizable más: `private function addOrderByCategoryName()`... porque probablemente querremos ordenar siempre nuestros datos de la misma manera. Dale el argumento habitual `QueryBuilder $qb = null`, devuelve un `QueryBuilder`, y el interior es bastante sencillo. Robaré el código anterior... déjame darle a "enter" para que se vea un poco mejor... y empezaré de la misma manera. Crearemos un `QueryBuilder`si es necesario, y luego diremos `->addOrderBy('category.name')`, seguido de`Criteria::DESC`, que hemos utilizado antes en nuestro método `search()`. Y sí, estamos ordenando en orden alfabético inverso porque, bueno, sinceramente no tengo ni idea de en qué estaba pensando cuando codifiqué esa parte.

[[[ code('638ca8d5f1') ]]]

Para utilizar esto, tenemos que separar un poco las cosas. Empieza con`$qb = $this->addOrderByCategoryName()` y no le pases nada. Luego pasa ese `$qb`a la segunda parte.

[[[ code('bfa05c0cd0') ]]]

En cuanto tengas varios métodos abreviados, no podrás encadenarlos todos... lo cual es un pequeño fastidio. Pero esto nos permite eliminar el`->addOrderBy()` de aquí abajo.

Si lo intentamos ahora... ¡la página sigue funcionando! Y si intentamos buscar algo en la página principal... ¡también funciona!

A continuación: vamos a conocer el sistema `Criteria`: una forma realmente genial de filtrar eficazmente las relaciones de colección dentro de la base de datos, manteniendo tu código sencillísimo.
