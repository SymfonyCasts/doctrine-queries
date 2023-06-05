# Consultas SQL sin procesar

El Constructor de consultas es divertido y potente. Pero si estás escribiendo una consulta supercompleja... puede ser difícil averiguar cómo transformarla al formato del Constructor de consultas. Si te encuentras en esta situación, siempre puedes recurrir a... ¡escribir SQL sin procesar! Yo no haría de esto mi primera opción, pero no hay grandes ventajas en pasar horas adaptando una consulta SQL bien escrita a un constructor de consultas.

## El objeto de conexión

Veamos cómo funcionan las consultas SQL sin procesar. Para empezar, comenta la consulta `->createQueryBuilder()`. A continuación, necesitamos obtener el objeto de bajo nivel Doctrine `Connection`. Podemos obtenerlo con `$conn = $this->getEntityManager()->getConnection()`. Añade `dd($conn)`al final para que podamos verlo.

[[[ code('454ac4f022') ]]]

Dirígete, actualiza y... ¡genial! Obtenemos un objeto `Doctrine\DBAL\Connection`.

La biblioteca Doctrine consta en realidad de dos partes principales. Primero hay una parte de nivel inferior llamada "DBAL", que significa "Database Abstraction Library" (Biblioteca de Abstracción de Bases de Datos). Actúa como una envoltura del PDO nativo de PHP y le añade algunas funciones.

La segunda parte de Doctrine es de la que nos hemos ocupado hasta ahora: es la parte de nivel superior llamada "ORM "o "Object Relational Mapper". Es cuando consultas seleccionando clases y propiedades... y obtienes de vuelta objetos.

Para esta consulta SQL sin procesar, vamos a tratar directamente con el objeto de nivel inferior `Connection`.

## Escribir y ejecutar la consulta

Di `$sql = 'SELECT * FROM fortune_cookie'`. Eso es lo más aburrido que pueden llegar a ser las consultas SQL. He utilizado `fortune_cookie` para el nombre de la tabla porque sé que, por defecto, Doctrine subraya mis entidades para hacer nombres de tabla. 

[[[ code('6615019ea3') ]]]

Ahora que tenemos la cadena de consulta, tenemos que crear un `Statement` con`$stmt = $conn->prepare()` y pasarle `$sql`.

[[[ code('33eeaca74f') ]]]

Esto crea un objeto `Statement`... que es algo así como el objeto `Query` que crearíamos con el `QueryBuilder` diciendo `->getQuery()` al final. Es... simplemente un objeto que utilizaremos para ejecutar esto. Hazlo con`$result = $stmt->executeQuery()`.

[[[ code('65b03b9414') ]]]

Por último, para obtener los datos reales del resultado, di `dd(result->)`... y hay varios métodos entre los que elegir. Utiliza `fetchAllAssociative()`.

[[[ code('117b0202d3') ]]]

Esto obtendrá todas las filas y nos dará cada una de ellas como una matriz asociativa.

Observa: vuelve a la pantalla y... ¡perfecto! ¡Obtenemos 20 filas por cada una de las 20 galletas de la suerte del sistema! Estos son los datos brutos procedentes de la base de datos.

## Una consulta más compleja

Bien, vamos a reescribir toda esta consulta QueryBuilder aquí arriba en SQL sin procesar. Para ahorrar tiempo, pegaré el producto final: una cadena larga... sin nada particularmente especial. Estamos seleccionando `SUM`, `AS fortunesPrinted`, la `AVG`, `category.name`, `FROM
fortune_cookie`, y luego hacemos nuestra `INNER JOIN` hasta `category`.

[[[ code('5c3a6e97cf') ]]]

La gran diferencia es que, cuando hacemos un `JOIN` con el QueryBuilder, podemos simplemente unirnos a través de la relación... y eso es todo lo que tenemos que decir. En SQL sin procesar, por supuesto, tenemos que ayudarle especificando que nos estamos uniendo a `category` y describiendo que nos estamos uniendo a `category.id = fortune_cookie.category_id`.

El resto es bastante normal... excepto `fortune_cookie.category_id = :category`. Aunque estemos ejecutando SQL sin procesar, no vamos a concatenar cosas dinámicas directamente en nuestra consulta. Es un gran error y, como sabemos, nos expone a ataques de inyección SQL. En su lugar, utiliza estos bonitos marcadores de posición como `:category`. Para rellenarlo, abajo, donde ejecutamos la consulta, pasa`'category' =>`. Pero esta vez, en lugar de pasar todo el objeto `$category` como hicimos antes, esto es SQL sin procesar, así que tenemos que pasar `$category->getId()`.

[[[ code('3ccfa38cc5') ]]]

De acuerdo Gira y comprueba esto. Ya está Así que escribir SQL sin procesar no parece tan impresionante... pero si tu consulta es lo suficientemente compleja, no dudes en probar esto.

## Utilizar bindValue()

Por cierto, en lugar de utilizar `executeQuery()` para pasar el `category`, podríamos, sustituirlo por `$stmt->bindValue()` para enlazar `category` con `$category->getId()`. Eso nos va a dar los mismos resultados que antes, así que tu llamada.

[[[ code('e5b65d7aa7') ]]]

Pero, hmm, ahora me doy cuenta de que el resultado es un array dentro de otro array. Lo que realmente queremos hacer es devolver sólo el array asociativo del único resultado. No hay problema: en lugar de `fetchAllAssociative()`, utiliza `fetchAssociative()`.

[[[ code('62bde2d8ac') ]]]

Y ahora... ¡precioso! Obtenemos sólo esa primera fila.

## Hidratación en un objeto

Ahora, quizá recuerdes que nuestro método debe devolver un objeto`CategoryFortuneStats` que hemos creado antes. ¿Podemos convertir el resultado de nuestra matriz en ese objeto? Claro, no es nada complicado, pero sí bastante fácil.

Podríamos devolver un `new CategoryFortuneStats()`... y luego coger las claves del array de `$result->fetchAssociative()`... y pasarlas como argumentos correctos.

O puedes ser aún más perezoso y utilizar el operador de dispersión junto con argumentos con nombre. Compruébalo: los argumentos se llaman `fortunesPrinted`, `fortunesAverage`, y`categoryName`. Aquí, son `fortunesPrinted`, `fortunesAverage`, y`name`... no `categoryName`. Vamos a arreglarlo. Aquí abajo, añade `as categoryName`. Y luego... ¡sí! Se llama `categoryName`.

Ahora podemos utilizar argumentos con nombre. Elimina el `dd()` y el otro retorno. A `CategoryFortuneStats`, pásale `...$result->fetchAssociative()`.

[[[ code('66f8ffdd8d') ]]]

Esto cogerá ese array y lo repartirá entre esos argumentos para que tengamos tres argumentos con nombre correcto... lo cual es bastante divertido.

Y ahora... ¡nuestra página funciona!

A continuación: Hablemos de organizar nuestro repositorio para poder reutilizar partes de nuestras consultas en varios métodos.
