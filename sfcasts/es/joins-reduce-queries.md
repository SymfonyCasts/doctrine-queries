# Consultas de reducción JOINs y addSelect

Cuando estamos en la página principal, vemos siete consultas. Tenemos una para obtener todas las categorías... y luego consultas adicionales para obtener todas las galletas de la suerte de cada categoría. Podemos ver esto en el perfilador. Ésta es la consulta principal `FROM category`... luego cada una de éstas de aquí abajo está seleccionando datos de galletas de la suerte para una categoría específica: 3, 4, 2, 6, etc.

## Relaciones de carga perezosa

Si has utilizado Doctrine, probablemente reconozcas lo que ocurre. Doctrine carga sus relaciones perezosamente. Sigamos la lógica. En `FortuneController`, empezamos consultando una matriz de `$categories`. En esa consulta, si nos fijamos, sólo está seleccionando datos de categorías: no datos de galletas de la suerte. Pero si entramos en la plantilla - `templates/fortune/homepage.html.twig` - hacemos un bucle sobre las categorías y finalmente llamamos a `category.fortuneCookies|length`.

## El problema N+1

En la tierra de PHP, estamos llamando al método `getFortuneCookies()` en `Category`. Pero hasta ahora, Doctrine aún no ha consultado los datos de `FortuneCookie` para esta Categoría. Sin embargo, en cuanto accedemos a la propiedad `$this->fortuneCookies`, mágicamente realiza esa consulta, diciendo básicamente:

> Dame todos los datos de `FortuneCookie` para esta categoría

Que... luego establece en la propiedad y nos la devuelve. Así que es en este momento dentro de Twig cuando se ejecuta esa segunda, tercera, cuarta, quinta, sexta y séptima consulta.

Esto se llama el "Problema N+1", en el que tienes "N" número de consultas para los elementos relacionados de tu página "más uno" para la consulta principal. En nuestro caso, es 1 consulta principal para las categorías más 6 consultas más para obtener los datos de las galletas de la suerte de esas 6 categorías.

Esto no es necesariamente un problema. Puede perjudicar el rendimiento de tu página... o no ser gran cosa. Pero si está ralentizando las cosas, podemos arreglarlo con `JOIN`. Después de todo, cuando consultamos las categorías, ya nos estamos uniendo a la tabla de galletas de la suerte. Así que... si sólo cogemos los datos de las galletas de la suerte en la primera consulta, ¿no podríamos construir toda esta página con esa única consulta? La respuesta es... ¡totalmente!

## Seleccionar los campos unidos

Para ver esto en acción, busca algo primero. Hago esto porque activará el método `search()` en nuestro repositorio, que ya tiene el `JOIN`. Aquí, como tenemos cinco resultados, hizo seis consultas.

Vale, ya nos estamos uniendo a `fortuneCookie`. Entonces, ¿cómo podemos seleccionar sus datos? Es deliciosamente sencillo. Y de nuevo, el orden no importa:`->addSelect('fortuneCookie')`.

¡Ya está! ¡Pruébalo! Las consultas se redujeron a una y la página sigue funcionando! Si abres el perfilador... y ves la consulta formateada... ¡sí! Se está uniendo a `fortune_cookie` y cogiendo los datos de `fortune_cookie` al mismo tiempo. ¡El problema "N+1" está resuelto!

## ¿Dónde se esconden los datos de la unión?

Pero quiero señalar una cosa clave. Como estamos dentro de`CategoryRepository`, cuando llamamos a `$this->createQueryBuilder('category')`, eso añade automáticamente un `->select('category')` a la consulta. Eso ya lo sabemos.

Sin embargo, ahora estamos seleccionando todos los datos de `category` y `fortuneCookie`. Pero... nuestra página sigue funcionando... lo que debe significar que, aunque estemos seleccionando datos de dos tablas, nuestra consulta sigue devolviendo lo mismo que antes: una matriz de objetos `Category`. No está devolviendo una mezcla de datos de `category` y`fortuneCookie`.

Este punto puede resultar un poco confuso, así que permíteme que lo desglose. Cuando llamamos a`createQueryBuilder()`, en realidad se añaden 2 cosas a nuestra consulta:`FROM App\Entity\Category as category` y `SELECT category`. Gracias a `FROM`,`Category` es nuestra "entidad raíz" y, a menos que empecemos a hacer algo más complejo, Doctrine intentará devolver objetos `Category`. Cuando llamamos a`->addSelect('fortuneCookie')`, en lugar de devolver una mezcla de categorías y galletas de la suerte, Doctrine básicamente coge los datos de `fortuneCookie` y los almacena para más adelante. Entonces, si alguna vez llamamos a `$category->getFortuneCookies()`, se da cuenta de que ya tiene esos datos, así que en lugar de hacer una consulta, los utiliza.

Lo realmente importante es que cuando utilizamos `->addSelect()` para coger los datos de un JOIN, no cambia lo que devuelve nuestro método. Aunque más adelante veremos ocasiones en las que utilizar `select()` o `addSelect()` sí cambia lo que devuelve nuestra consulta.

Vale, acabamos de utilizar un JOIN para reducir nuestra consulta de 7 a 1. Sin embargo, como sólo vamos a contar el número de galletas de la suerte de cada categoría, hay otra solución. Hablemos ahora de las relaciones EXTRA_LAZY.
