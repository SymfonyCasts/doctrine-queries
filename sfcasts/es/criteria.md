# Criterios: Filtrar colecciones de relaciones

En la página de presentación de categorías, hacemos un bucle sobre todas las galletas de la suerte de esa categoría. Veamos la plantilla: `templates/fortune/showCategory.html.twig`. Aquí está: hacemos un bucle sobre `category.fortuneCookies` y mostramos algunas cosas.

[[[ code('657d74265f') ]]]

Pero... hay un problema. Abre la entidad `FortuneCookie`. Tiene una bandera`bool $discontinued`. De vez en cuando, tenemos que dejar de producir una galleta de la fortuna específica... por una razón u otra. Como aquella vez que teníamos una galleta de la suerte que decía "Serás feliz... hasta que te des cuenta de que la realidad es una ilusión". Esa se escapó del control de calidad. Cuando esto ocurre, ponemos `discontinued` en verdadero.

En este momento, hacemos un bucle con todas las galletas de la suerte de una categoría: ¡incluidas las galletas actuales y las descatalogadas! Pero a la dirección sólo le interesan las galletas de la suerte actuales. Necesitamos una forma de ocultar las descatalogadas. ¿Cómo podemos hacerlo?

En el controlador de esta página - `FortuneController` - podríamos crear una consulta independiente de `$fortuneCookieRepository` con

> WHERE categoría = :categoría y descatalogado = false.

Pero... ¡eso es penoso! ¡Hacer un bucle sobre `category.fortuneCookies` es tan fácil! ¿Realmente necesitamos volver al controlador, crear una consulta personalizada y pasar los resultados como una nueva variable Twig? ¿No podríamos utilizar de algún modo el objeto `category`... pero filtrando las cookies descatalogadas? ¡Por supuesto! Y si lo hacemos correctamente, podemos hacerlo de forma realmente eficiente.

El primer paso es opcional, pero en el controlador, vuelve a cambiar`->findWithFortunesJoin()` por sólo `->find()`. Hago esto -que elimina la unión- sólo para que sea más fácil ver el resultado final de lo que vamos a hacer.

[[[ code('0cfb9ffc84') ]]]

Hacer esto no cambia la página... excepto que nuestras consultas pasan a ser tres. Es decir, una consulta para el `Category`, nuestra consulta personalizada que estamos haciendo, y luego una consulta para todas las fortunas dentro de este `Category`.

## Añadir un método de entidad personalizado para las cookies descatalogadas

Recuerda el objetivo: queremos poder llamar a algo en el objeto `Category` para recuperar las galletas de la fortuna relacionadas... pero ocultando las descatalogadas.

Abre la entidad `Category` y busca `getFortuneCookies()`. Ahí lo tienes. A continuación, añade un nuevo método llamado `getFortuneCookiesStillInProduction()`. Éste, al igual que el método normal, devolverá una Doctrine `Collection`. Y... sólo para ayudar a mi editor, copia el documento `@return` anterior para decir que se trata de un `Collection` de objetos`FortuneCookie`.

[[[ code('c83c2869a6') ]]]

Entonces... ¿qué hacemos dentro? Podríamos hacer un bucle sobre`$this->fortuneCookies as $fortuneCookie` y crear una matriz de objetos que no estén discontinuados. ¡Fácil!

Pero... en cuanto empecemos a trabajar con `$this->getFortuneCookies()`, eso hará que Doctrine consulte cada galleta de la suerte relacionada. ¿Ves el problema? Puede que estemos pidiendo a Doctrine que consulte y prepare 100 objetos `FortuneCookie`... aunque esta colección final `$inProduction` sólo contenga 10. ¡Qué desperdicio!

Lo que realmente queremos hacer es decirle a Doctrine que cuando realice la consulta de las galletas de la suerte relacionadas, añada un `WHERE discontinued = false`extra a esa consulta.

## Hola Criterios

Pero... ¿cómo demonios lo hacemos? Doctrine realiza esa consulta de forma automática y... mágica en algún lugar en segundo plano. Aquí es donde resulta útil el sistema de criterios.

Funciona así `$criteria = Criteria::` - el de`Doctrine\Common\Collections` - `create()`.

[[[ code('69b5dd6206') ]]]

Este objeto es un poco como el de `QueryBuilder`, pero no exactamente igual. Podemos decir `->andWhere()` y luego volver a utilizar `Criteria::` con `expr()->`. Este`expr()` o "expresión" nos permite, más o menos, construir la cláusula WHERE. Tiene métodos como `in`, `contains` o `gt` para "mayor que". Queremos `eq()` para "igual a". Dentro, digamos `discontinued`, `false`.

[[[ code('bfdeee0848') ]]]

Vale, esto, por sí mismo, sólo crea un objeto que "describe" una cláusula `WHERE` que podría añadirse a alguna otra consulta. Para utilizarlo,`return $this->fortuneCookies->matching($criteria)`.

[[[ code('2835162724') ]]]

Genial, ¿eh? Estamos diciendo:

> ¡Eh, Doctrine! Toma esta colección, pero devuelve sólo las que coincidan con este criterio.

Y como veremos dentro de un minuto, ¡esto modificará la consulta para obtener esas galletas de la suerte!

Para utilizar este método, en `showCategory.html.twig`, sustituye el bucle`category.fortuneCookies` por `category.fortuneCookiesStillInProduction`.

[[[ code('84f8e9f4ea') ]]]

¡Vamos a hacerlo! Actualiza, y... En realidad, no sé si alguno de ellos está descatalogado, ¡pero pasó de tres a dos! ¿Y lo mejor? ¡Echa un vistazo a la consulta! Aquí está la primera para la categoría, aquí está la nuestra personalizada... pero fíjate en esta última consulta. Cuando preguntamos por las "galletas de la suerte aún en producción", consulta desde `fortune_cookie`, donde `category =` nuestra categoría y donde`t0.discontinued` ¡es falso! Así que ha hecho la consulta más eficiente para obtener sólo las galletas de la suerte que necesitamos. Es asombroso.

## Organizar tu código de criterios en el repositorio

Ahora, un pequeño inconveniente es que... Normalmente me gusta mantener mi lógica de consulta dentro de un repositorio... no en medio de una entidad. Afortunadamente, podemos moverla allí.

Como esto trata de galletas de la suerte, abre`FortuneCookieRepository` y, en cualquier lugar, añade un nuevo `public static function` llamado... ¿qué tal `createFortuneCookiesStillInProductionCriteria()`. Esto devolverá un objeto`Criteria`.

Ahora, coge la declaración `$criteria` de la entidad... y devuélvela.

[[[ code('87afa6495f') ]]]

## ¿El método es estático?

Y sí, se trata de un método `static`... que no utilizo demasiado a menudo. Hay dos razones para ello. En primer lugar, estos objetos `Criteria` en realidad no hacen consultas... y no dependen de ningún dato o servicio. Por tanto, este método puede ser estático. En segundo lugar, y más importante, no tenemos acceso al objeto repositorio desde dentro de `Category`. Así que... si queremos llamar a un método de un repositorio, tiene que ser `static`. Esto es algo especial que suelo hacer en mis repositorios sólo para esta situación de criterios.

Volviendo a la entidad, digamos que `$criteria` es igual a`FortuneCookieRepository::createFortuneCookiesStillInProductionCriteria()`.

[[[ code('2a4e54ba70') ]]]

Centralización lógica, ¡comprobado! Ah, e incluso podemos reutilizar estos objetos `Criteria` dentro de un `QueryBuilder`. Veamos... No tengo un buen ejemplo... así que... en este método, arriba, hagamos de cuenta que estoy creando un `QueryBuilder` con`$this->createQueryBuilder('fortune_cookie')`. Para añadir los criterios es...`->addCriteria(self::createFortuneCookiesStillInProduction())`.

Así que, aunque el sistema de criterios es un poco diferente del QueryBuilder normal, podemos reutilizarlos en todas partes. Ah, y comprobemos que todo sigue funcionando. ¡Ya está!

## Utilizar el sistema de criterios en el controlador + EXTRA_LAZY Fetch

En la página de inicio, tenemos un problema similar. Aquí dice "Proverbios(3)", pero si hacemos clic en él, aparecen dos. ¿Qué ocurre aquí? En`homepage.html.twig`... veamos... ah, sí. Estamos haciendo un bucle sobre `categories`, y luego llamando a `category.fortuneCookies|length` que, como sabemos, devuelve todas las galletas de la fortuna. Cámbialo a `fortuneCookiesStillInProduction`.

[[[ code('16709b79ac') ]]]

De vuelta a la página de inicio, observa este "(3)". Debería bajar a 2, y... lo hace. Pero eso ni siquiera es lo mejor. Abre la consulta para ello. Recuerda que, gracias a nuestro fetch `EXTRA_LAZY`, como sólo estamos contando el número de galletas de la suerte, sabe hacer una consulta superrápida `COUNT`. Y gracias al sistema de criterios, está seleccionando `COUNT FROM fortune_cookies WHERE` la `category` = nuestra categoría y`discontinued = false`. ¡Vaya!

Siguiente: Queremos ocultar las galletas de la suerte descatalogadas de todas partes de nuestro sitio. ¿Hay alguna forma de engancharnos a Doctrine y añadir esa cláusula `WHERE` automáticamente... en todas partes? La hay. Se llama filtros.
