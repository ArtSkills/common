# RestApi роутер на основе OpenApi аннотаций

Класс [RestApiRouteBuilder](RestApiRouteBuilder.php) применяется для автоматической настройки роутинга на основе
OpenApi PHPDoc аннотаций: https://zircote.github.io/swagger-php/#links

Как использовать:

```php
Router::scope('/', function (RouteBuilder $routes) {
    ...
    $openApi = new \ArtSkills\Routing\RestApiRouteBuilder($routes);
    $openApi->build('Predict'); // Прописываем маршруты для всех контроллеров в папке Controller/Predict, а также его подпапок
}
```

Как использовать в рамках плагина:
```php
Router::scope('/', function (RouteBuilder $routes) {
    ...
    $openApi = new \ArtSkills\Routing\PluginRestApiRouteBuilder($routes, 'PublicPages');
    $openApi->build(''); // Прописываем маршруты для всех контроллеров в папке Controller
}
```

Пример описания в контроллере:

```php
/**
 * @OA\Delete(
 *  path = "predict/wbConfig/{wbConfigId}/clearAll",
 *  tags = {"WbConfig"},
 *  summary = "Удаление всех расчётов у кабинета",
 *  operationId = "clearAll",
 *  @OA\Parameter(
 *      in = "path",
 *      name = "wbConfigId",
 *      description = "Идентификатор кабинета",
 *      required = true,
 *      @OA\Schema(type="integer")
 *  ),
 *  @OA\Response(
 *    response = 200,
 *    description = "Результат запроса",
 *    @OA\JsonContent(ref = "#/components/schemas/ApiResponse")
 *  )
 * )
 *
 * @param int $wbConfigId
 */
public function clearAll(int $wbConfigId): ?Response
{
    return $this->_sendJsonOk(['clearAll ' . $wbConfigId]);
}
```

Все параметры из описания `path` передаются в аргументы контроллера, допустимые типы параметров:

- `integer` - цифры без знака.
- `string` - английский алфавит, цифры, символы `-`,`_`,`.`.

## Конвенция роутера

- **Один метод** контроллера на вход может обрабатывать только **одно** событие - GET, POST, PUT, DELETE.
- В `Response` аннотации должен быть описан успешный JSON ответ (работаем только по JSON протоколу).
- Постфикс `.json` в аннотации `path` запрещён, т.к. он не имеет смысла - API всегда отдаёт только JSON, и лишние
  символы в пути не
  нужны.
- Для корректного построения дерева Swagger директива `path` содержит относительный путь (без "/" вначале).
- Все пути прописываются в OpenApi относительно `ServerName` (в конфигурации проекта `app.php`)
