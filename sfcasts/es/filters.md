# Filtros: Modificar consultas automáticamente

Gracias a nuestro nuevo y genial método, podemos filtrar las galletas de la suerte descatalogadas. Pero, ¿y si queremos aplicar un criterio como éste de forma global a todas las consultas de una tabla? Por ejemplo, diciéndole a Doctrine que siempre que busque galletas de la suerte, añada `WHERE discontinued = false` a esa consulta.

Parece una locura. Y, sin embargo, es totalmente posible. Para demostrarlo, volvamos a dejar nuestras dos plantillas como estaban antes. Y ahora... si entramos en "Proverbios"... ¡sí! Vuelven a aparecer las 3 fortunas.

## Hola Filtros

Para aplicar una cláusula WHERE "global", podemos crear un filtro Doctrine. En el directorio `src/`, añade un nuevo directorio llamado `Doctrine/` para la organización. Dentro de él, añade una nueva clase llamada `DiscontinuedFilter`. Haz que ésta extienda `SQLFilter`... luego ve a Código -> Generar (o "comando" + "N" en un Mac) y selecciona "Implementar Métodos" para generar el único método que necesitamos `addFilterConstraint()`.

[[[ code('87abc8f970') ]]]

Una vez que tengamos todo listo, Doctrine llamará a `addFilterConstraint()` cuando esté construyendo cualquier consulta y nos pasará información sobre la entidad que estamos consultando: esto es `ClassMetadata`. También nos pasará el`$targetTableAlias`, que necesitaremos dentro de un minuto para modificar la consulta.

Ah, y para evitar un aviso de obsoleto, añade un tipo de retorno `string` al método.

Para ver mejor lo que ocurre, hagamos nuestra cosa favorita y`dd($targetEntity, $targetTableAlias)`.

[[[ code('3bffbba387') ]]]

## Activar el filtro

Pero... cuando nos dirigimos y actualizamos la página... ¡no pasa nada! A diferencia de otras cosas, los filtros no se activan automáticamente con sólo crear la clase. Activarlo es un proceso de dos pasos.

En primer lugar, en `config/packages/doctrine.yaml`, tenemos que decirle a Doctrine que el filtro existe. En cualquier lugar directamente debajo de la clave `orm`, añade `filters` y luego`fortuneCookie_discontinued`. Esa cadena puede ser cualquier cosa... y verás cómo la utilizamos en un minuto. Pon esto en la clase: `App\Doctrine\DiscontinuedFilter`.

[[[ code('24003e34ad') ]]]

Muy fácil.

Esto ya está registrado en Doctrine... pero como puedes ver aquí, todavía no se llama. El segundo paso es activarlo donde quieras. En algunos casos, puede que quieras que este `DiscontinuedFilter` se utilice en una sección de tu sitio, pero no en otra.

Abre el controlador... allá vamos... dirígete a la página principal y autoconecta`EntityManagerInterface $entityManager`. Luego, justo encima, di`$entityManager->getFilters()` seguido de `->enable()`. Luego pásale la misma clave que usamos en `doctrine.yaml` - `fortuneCookie_discontinued`. Ve a cogerla... y pégala.

[[[ code('f3f75ced71') ]]]

Con un poco de suerte, todas las consultas que hagamos después de esta línea utilizarán ese filtro. Dirígete a la página principal y... ¡sí! ¡Ha dado en el clavo!

Y ¡woh! Este `ClassMetadata` es un gran objeto que lo sabe todo sobre nuestra entidad. Aquí abajo, aparentemente, para cualquier consulta que estemos haciendo primero, el alias de la tabla -el alias que se está utilizando en la consulta- es `c0_`. ¡De acuerdo! ¡Manos a la obra!

## Añadir el filtro Logoc

Como ya he dicho, esto se llamará para cada consulta. Así que tenemos que tener cuidado de añadir nuestra cláusula `WHERE` sólo cuando estemos consultando galletas de la suerte. Para ello, di if `$targetEntity->name !== FortuneCookie::class`, then`return ''`.

[[[ code('d11f8a2329') ]]]

Este método devuelve un `string`... y esa cadena se añade básicamente a una cláusula `WHERE`. En la parte inferior, `return sprintf('%s.discontinued = false')`, pasando `$targetTableAlias` por el comodín.

[[[ code('82b4f5a720') ]]]

¿Listo para comprobarlo? En la página de inicio, el recuento de "Proverbios" debería pasar de 3 a 2. Y... ¡así es! Compruébalo en la consulta. ¡Sí! Tiene`t0.discontinued = false` dentro de cada búsqueda de galletas de la fortuna. ¡Es increíble!

## Pasar parámetros a los filtros

Una cosa complicada de estos filtros es que no son servicios. Así que no pueden tener un constructor... simplemente no está permitido. Si necesitamos pasarle algo -como alguna configuración- tenemos que hacerlo de otra manera. Por ejemplo, supongamos que a veces queremos ocultar las cookies descatalogadas... pero otras veces, queremos mostrar sólo las descatalogadas - a la inversa. Básicamente, queremos poder cambiar este valor de `false` a `true`.

Para ello, cambia esto a `%s` y rellénalo con `$this->getParameter()`... pasando una cadena que me estoy inventando: `discontinued`. Verás cómo se utiliza en un minuto.

[[[ code('ef29324a88') ]]]

Ahora bien, normalmente no añado `%s` a mis consultas... porque eso puede permitir ataques de inyección SQL. En este caso, está bien, pero sólo porque el método `getParameter()`está diseñado para escapar del valor por nosotros. En cualquier otra situación, evítalo.

Si nos dirigimos y lo intentamos ahora... ¡obtendremos un error gigantesco! ¡Sí!

> El parámetro 'discontinued' no existe.

¡Es cierto! En cuanto leas un parámetro, tienes que pasarlo cuando actives el filtro. Hazlo con `->setParameter('discontinued')`... y digamos`false`.

[[[ code('07ad8293a9') ]]]

Si recargamos ahora... ¡funciona! ¿Qué ocurre si lo cambiamos por`true`? Recarga de nuevo y... ¡sí! ¡El número ha cambiado! ¡Ya gobernamos!

## Activar esto globalmente

Aunque... probablemente estés pensando:

> Ryan, tío, sí, esto mola... pero ¿no puedo activar este filtro globalmente...
> sin necesidad de poner este código en cada controlador?

¡Por supuesto! Vuelve al controlador y comenta esto.

Al hacerlo, el número vuelve a ser 3. Para activarlo globalmente, vuelve a la configuración: vamos a complicarlo un poco más. Pon esto en una nueva línea, ponlo en `class` y luego pon `enabled` en `true`.

Y así de fácil, esto estará habilitado en todas partes... aunque aún podrías deshabilitarlo en controladores específicos. Ah, pero ya que tenemos el parámetro, también necesitamos `parameters`, con `discontinued: false`.

[[[ code('f09fc4f1d3') ]]]

Y... ¡ya está! Los filtros molan.

Lo siguiente: Hablemos de cómo utilizar el práctico operador `IN` con una consulta.
