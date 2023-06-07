# Seleccionar en un nuevo objeto DTO

Tener la flexibilidad de seleccionar los datos que queramos es genial. Tratar con la matriz asociativa que obtenemos de vuelta es... ¡menos alucinante! Me gusta trabajar con objetos siempre que sea posible. Afortunadamente, Doctrine nos ofrece una forma sencilla de mejorar esta situación: consultamos los datos que queremos... pero le decimos que nos dé un objeto.

## Crear el DTO

En primer lugar, necesitamos crear una nueva clase que contenga los datos de nuestra consulta. Crearé un nuevo directorio llamado `src/Model/`... pero podría llamarse como quieras. Llama a la clase... ¿qué tal `CategoryFortuneStats`.

El único propósito de esta clase es contener los datos de esta consulta específica. Así que añade un `public function __construct()` con unas cuantas propiedades `public` para simplificar:`public int $fortunesPrinted`, `public float $fortunesAverage`, y`public string $categoryName`.

[[[ code('82e7f4dc59') ]]]

¡Estupendo!

De vuelta al repositorio, en realidad no necesitamos ninguna magia de Doctrine para utilizar esta nueva clase. Podríamos consultar la matriz asociativa, devolver `new CategoryFortuneStats()`y pasarle cada clave.

Es una gran opción, muy sencilla y además este método del repositorio devolvería un objeto en lugar de un array. Pero... Doctrine lo hace aún más fácil gracias a una función poco conocida.

Añade un nuevo `->select()` que contendrá todas estas selecciones en una. Añade también un `sprintf()`: verás por qué en un minuto. Dentro, ¡mira esto! Di`NEW %s()` y luego pasa `CategoryFortuneStats::class` por ese marcador de posición. Básicamente, estamos diciendo `NEW App\Model\CategoryFortuneStats()`... Sólo quería evitar escribir ese nombre de clase tan largo.

Dentro de `NEW`, coge cada una de las 3 cosas que queremos seleccionar y pégalas, como si las pasáramos directamente como primer, segundo y tercer argumento al constructor de nuestra nueva clase.

[[[ code('d450ea1fd4') ]]]

¿No es genial? ¡Vamos a `dd($result)` para ver cómo queda!

## Sin aliasing con NEW

Si nos dirigimos y actualizamos... oh... me aparece un error: `T_CLOSE_PARENTHESIS, got 'AS'`. Cuando seleccionamos datos en un objeto, el aliasing ya no es necesario... ni está permitido. Y tiene sentido: Doctrine pasará lo que sea esto al primer argumento de nuestro constructor, esto al segundo argumento y esto al tercero. Como los alias ya no tienen sentido... elimínalos.

[[[ code('b3c856cd48') ]]]

Si lo comprobamos ahora... ¡lo tengo! ¡Me encanta! ¡Tenemos un objeto con nuestros datos dentro!

Vamos a celebrarlo limpiando nuestro método. En lugar de un `array`, vamos a devolver un `CategoryFortuneStats`. Elimina también el `dd($result)` de aquí abajo.

[[[ code('e6e552dc95') ]]]

De vuelta en el controlador, para mostrar lo bonito que es esto, cambia `$result` por... qué tal `$stats`. Entonces podemos utilizar `$stats->fortunesPrinted`, `$stats->fortunesAverage`, y `$stats->categoryName`.

[[[ code('fff67df61a') ]]]

Ahora que lo hemos arreglado un poco, comprobemos si sigue funcionando. Y... funciona.

Siguiente: A veces las consultas son tan complejas... que la mejor opción es escribirlas en SQL nativo. Hablemos de cómo hacerlo.
