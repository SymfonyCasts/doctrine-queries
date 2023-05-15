# Doctrina DQL

¡Hola amigos! Gracias por acompañarme en este tutorial, que trata sobre los entresijos de la ejecución de consultas en Doctrine. Parece sencillo... y lo es durante un tiempo. Pero entonces empiezas a añadir uniones, agrupaciones, a tomar sólo datos específicos en lugar de objetos completos, recuentos... y... bueno... ¡se pone interesante! Este tutorial trata de profundizar en todas esas cosas buenas, incluida la ejecución de consultas SQL nativas, el lenguaje de consulta Doctrine, el filtrado de colecciones, la solución del problema "N + 1" y mucho más.

Estoy entusiasmado. Así que ¡manos a la obra!

## Configuración del proyecto

Para INSERTAR la mayor cantidad de conocimientos de consulta en tu cerebro, te recomiendo encarecidamente que codifiques conmigo. Puedes descargar el código del curso desde esta página. Después de descomprimirlo, tendrás un directorio `start/` con el mismo código que ves aquí. También hay un ingenioso archivo`README.md` con todas las instrucciones de configuración. El último paso será ir a tu terminal, entrar en el proyecto y ejecutar

```terminal
symfony serve -d
```

para iniciar un servidor web integrado en `https://127.0.0.1:8000`. Haré trampas, haré clic en eso, y... di "hola" a nuestra última iniciativa: Consultas de Fortuna. Verás, tenemos un negocio paralelo de distribución multinacional de galletas de la fortuna... y esta elegante aplicación nos ayuda a hacer un seguimiento de todas las fortunas que hemos concedido a nuestros clientes.

Son exactamente 2 páginas: éstas son las categorías, y puedes hacer clic en una para ver su fortuna... incluyendo cuántas se han impreso. Se trata de un proyecto Symfony 6.2, y en este punto, no podría ser más sencillo. Tenemos una entidad `Category`, una entidad `FortuneCookie`, exactamente un controlador y ninguna consulta extravagante.

Nota al margen: este proyecto utiliza MySQL... pero casi todo de lo que vamos a hablar funcionará con Postgres o cualquier otra cosa.

## Crear nuestro primer método de repositorio personalizado

Hablando de ese único controlador, aquí en la página de inicio, puedes ver que estamos autocableando `CategoryRepository` y utilizando la forma más sencilla de consultar algo en Doctrine: `findAll()`. 

[[[ code('a28ee965b9') ]]]

Nuestro primer truco será super sencillo, pero interesante. Quiero reordenar estas categorías alfabéticamente por nombre. Una forma sencilla de hacerlo es cambiando `findAll()` por `findBy()`. Esto se utiliza normalmente para encontrar elementos DONDE coinciden con un criterio - algo como `['name' => 'foo']`.

Pero... también puedes dejarlo vacío y aprovechar el segundo argumento: una matriz de orden por. Así que podríamos decir algo como `['name' => 'DESC']`.

Pero... cuando necesito una consulta personalizada, me gusta crear métodos de repositorio personalizados para centralizarlo todo. Dirígete al directorio `src/Repository/` y abre`CategoryRepository.php`. Dentro, podemos añadir los métodos que queramos. Vamos a crear uno nuevo llamado `public function findAllOrdered()`. Éste devolverá un `array`... e incluso anunciaré que se trata de un array de objetos `Category`.

[[[ code('12b0cf5ed9') ]]]

Antes de rellenar esto, aquí atrás... llámalo: `->findAllOrdered()`.

[[[ code('96068f769d') ]]]

¡Encantado!

## Hola DQL (Lenguaje de consulta Doctrine)

Si has trabajado antes con Doctrine, probablemente esperas que utilice el Constructor de consultas. Hablaremos de ello dentro de un momento. Pero quiero empezar de forma aún más sencilla. Doctrine trabaja con muchos sistemas de bases de datos, como MySQL, Postgres, MSSQL, etc. Cada uno de ellos tiene un lenguaje SQL, pero no todos son iguales. Así que Doctrine tuvo que inventar su propio lenguaje similar a SQL llamado "DQL", o "Doctrine Query Language". ¡Es divertido! Se parece mucho a SQL. La mayor diferencia es probablemente que nos referimos a clases y propiedades en lugar de a tablas y columnas.

Escribamos una consulta DQL a mano. Digamos que `$dql` es igual a`SELECT category FROM App\Entity\Category as category`. Estamos asociando la clase`App\Entity\Category` a la cadena `category` de la misma forma que asociaríamos el nombre de una tabla a algo en SQL. Y aquí, con sólo seleccionar `category`, estamos seleccionando todo, lo que significa que devolverá objetos `Category`.

Y ya está Para ejecutarlo, crea un objeto `Query` con`$query = $this->getEntityManager()->createQuery($dql);`. Luego ejecútalo con`return $query->getResult()`.

[[[ code('542dd24d88') ]]]

También hay un `$query->execute()`, y aunque realmente no importa, yo prefiero`getResult()`.

Cuando vayamos a probarlo... ¡no cambia nada! ¡Funciona! ¡Acabamos de utilizar DQL directamente para hacer esa consulta!

## Añadiendo el DQL ORDER BY

Entonces... ¿qué aspecto tiene añadir el `ORDER BY`? ¡Probablemente puedas adivinar cómo empieza `ORDER BY`! 

Lo interesante es que, para ordenar por `name`, no vamos a hacer referencia a la columna `name` de la base de datos. No, nuestra entidad `Category` tiene una propiedad `$name`, y es a ella a la que nos vamos a referir. Probablemente la columna también se llame `name`... pero podría llamarse `unnecessarily_long_column_name`y seguiríamos ordenando por la propiedad `name`.

La cuestión es que, como tenemos una propiedad `$name`, aquí podemos decir`ORDER BY category.name`.

Ah, y en SQL, utilizar el alias es opcional: puedes decir `ORDER BY name`. Pero en DQL, es obligatorio, así que debemos decir `category.name`. Por último, añade `DESC`.

[[[ code('5e0bbb1f38') ]]]

Si ahora recargamos la página... ¡está ordenada alfabéticamente!

## La transformación DQL -> SQL

Cuando escribimos DQL, entre bastidores, Doctrine lo convierte en SQL y luego lo ejecuta. Busca qué sistema de base de datos estamos utilizando y lo traduce al lenguaje SQL de ese sistema. Podemos ver el SQL con `dd()` (por "volcar y morir") `$query->getSQL()`.

[[[ code('5e0bbb1f38') ]]]

Y... ¡ahí está! ¡Esa es la consulta SQL real que se está ejecutando! Tiene este feo alias`c0_`, pero es lo que esperamos: coge todas las columnas de esa tabla y las devuelve. ¡Es genial!

Por cierto, también puedes ver la consulta dentro de nuestro perfilador. Si quitamos esa depuración y refrescamos... aquí abajo, podemos ver que estamos haciendo siete consultas. Hablaremos de por qué hay siete dentro de un rato. Pero si hacemos clic en ese pequeño icono... ¡bum! ¡Ahí está la primera consulta! También puedes ver una versión bonita de la misma, así como una versión que puedes ejecutar. Si tienes alguna variable dentro de las cláusulas `WHERE`, la versión ejecutable las rellenará por ti.

Siguiente: Normalmente no escribimos DQL a mano. En lugar de eso, lo construimos con el Generador de consultas. Veamos qué aspecto tiene.