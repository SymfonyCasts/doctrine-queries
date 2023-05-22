# JOINs

Tenemos este genial método `->andWhere()` que busca en las propiedades `name` o`iconKey` de la entidad `Category`. Pero, ¿podríamos buscar también en los datos de las galletas de la suerte dentro de cada categoría? ¡Claro que sí!

Veamos cómo se establece esa relación. En `Category`, tenemos una relación `OneToMany`sobre una propiedad llamada `$fortuneCookies` sobre la entidad `FortuneCookie`.

## Pensando en los JOINs en Doctrine

Si pensamos en el problema desde la perspectiva de la base de datos, para actualizar nuestra cláusula`WHERE` para incluir `WHERE fortune_cookie.fortune = :searchTerm`, primero necesitamos `JOIN` a la tabla `fortune_cookie`.

Y eso es lo que vamos a hacer en Doctrine... excepto con un giro. En lugar de pensar en unir tablas, vamos a pensar en unir clases de entidades. Esto puede parecer raro al principio, pero es genial. En este caso, queremos `JOIN` a través de esta propiedad `fortuneCookies` a la entidad`FortuneCookie`.

## Utilizando leftJoin()

¡Vamos a hacerlo! Volviendo a `CategoryRepository`... podemos añadir la unión en cualquier parte de la consulta. A diferencia de SQL, al QueryBuilder no le importa el orden en que hagas las cosas. Añade `->leftJoin()` porque estamos uniendo desde una categoría a muchas galletas de la suerte. Pasa esto a `category.fortuneCookies` y luego a `fortuneCookie`, que será el alias de la entidad unida.

[[[ code('97e150d684') ]]]

Cuando decimos `category.fortuneCookies`, nos referimos a la propiedad `fortuneCookies`. Lo bueno es que... ¡esto es todo lo que necesitamos! No necesitamos decirle a Doctrine a qué entidad o tabla nos estamos uniendo... y no necesitamos el`ON fortune_cookie.category_id = category.id` que veríamos normalmente en SQL. No necesitamos nada de esto porque Doctrine ya tiene esa información en el mapeo`OneToMany`. Sólo decimos "unir a través de esta propiedad" y ¡él hace el resto!

Una cosa a tener en cuenta, de la que hablaremos más adelante, es que, al unirnos a algo, no estamos seleccionando más datos. Sólo estamos haciendo que las propiedades de `FortuneCookie` estén disponibles dentro de nuestra consulta. Esto significa que podemos hacer que `->andWhere()` sea aún más largo. Añade `OR fortuneCookie` (utilizando el nuevo alias de la unión) `.fortune` (porque `fortune` es el nombre de la propiedad de `FortuneCookie`que almacena el texto) `LIKE :searchTerm`.

[[[ code('f1d38c9b46') ]]]

¡Listo! Vuelve al sitio. Una de mis fortunas tiene la palabra "conclusión". Gira a la página de inicio, busca "conclusión" y... ¡lo tienes! ¡Parece que tenemos al menos una coincidencia en nuestra categoría "Proverbios"! ¡Falta la conclusión!

Pero si haces clic en el icono de la base de datos de la barra de herramientas de depuración web... esta página tiene dos consultas. La primera es para la categoría - tiene `FROM category` e incluye el `LEFT JOIN` que acabamos de añadir. La segunda es `FROM fortune_cookie`.

Y si vamos a la página principal sin buscar, hay siete consultas en total: una para obtener todas las categorías... y luego otras 6 para encontrar las galletas de la suerte de cada una de las seis categorías. Esto se denomina el problema de la consulta N+1. Vamos a hablar de él a continuación y a solucionarlo con uniones.
