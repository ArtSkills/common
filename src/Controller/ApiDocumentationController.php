<?php
declare(strict_types=1);

namespace ArtSkills\Controller;

use ArtSkills\Lib\Env;
use ArtSkills\Lib\Url;
use Cake\Cache\Cache;
use Cake\Http\Response;
use OpenApi\Annotations\Contact;
use OpenApi\Annotations\Info;
use OpenApi\Annotations\Server;
use OpenApi\Generator;
use OpenApi\Util;

/**
 * Формируем документацию по API
 *
 * Конфигурация описывается в файле app.php:
 * ```php
 * 'apiInfo' => [
 *    'title' => 'Eggheads.Solutions Api',
 *    'description' => 'Eggheads.Solutions Api. Документ сформирован автоматически, онлайн <a href="https://github.com/swagger-api/swagger-codegen/tree/3.0.0#online-generators">генератор кода API</a>',
 *    'version' => '1',
 *    'contact' => [
 *       'email' => 'tune@eggheads.solutions',
 *       'url' => '/apiDocumentation.json', // путь к контроллеру
 *    ],
 * ],
 * ```
 */
class ApiDocumentationController extends Controller
{
    protected const DOCUMENTATION_CACHE_PROFILE = 'default';

    /**
     *
     * Формируем документацию как в HTML, так и в JSON формате
     * @OA\Get(
     *  path = "apiDocumentation.json",
     *  tags = {"Documentation"},
     *  summary = "Документация по API",
     *  operationId = "apiDocumentation",
     *  @OA\Response(
     *    response = 200,
     *    description = "Результат запроса",
     *    @OA\JsonContent(ref = "#/components/schemas/ApiResponse")
     *  )
     * )
     *
     * @return Response|null
     */
    public function index(): ?Response
    {
        if ($this->_responseExtension !== self::REQUEST_EXTENSION_DEFAULT) {
            return $this->_sendTextResponse(json_encode($this->_getJson(), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT), 'json');
        } else {
            return $this->_sendTextResponse($this->_getHtml(), 'html');
        }
    }

    /**
     * Формируем выдачу в формате JSON
     * @OA\Info(
     *  title = "",
     *  description =  "",
     *  @OA\Contact(
     *      email = ""
     *  ),
     *  version = "1.0"
     * )
     *
     * @return array
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    private function _getJson(): array
    {
        return Cache::remember('ApiDocumentationJson#' . CORE_VERSION, function () {
            $apiInfo = $this->_getApiInfo();
            $swagger = Generator::scan(Util::finder([APP, __DIR__, PLUGINS . '*/src/'], Env::getApiDocumentationExclude() ?? []));
            $swagger->info = new Info([
                'title' => $apiInfo['title'],
                'description' => $apiInfo['description'],
                'version' => $apiInfo['version'],
                'contact' => new Contact([
                    'email' => $apiInfo['contact']['email'],
                    'url' => Url::withDomainAndProtocol($apiInfo['contact']['url']),
                ]),
            ]);

            $swagger->servers = [
                new Server([
                    'url' => Url::withDomainAndProtocol(),
                ]),
            ];

            return json_decode(json_encode($swagger), true);
        }, static::DOCUMENTATION_CACHE_PROFILE);
    }

    /**
     * Формируем выдачу в формате HTML
     *
     * @see https://github.com/swagger-api/swagger-ui/blob/master/docs/usage/installation.md#unpkg
     *
     * @return string
     */
    private function _getHtml(): string
    {
        $apiInfo = $this->_getApiInfo();
        $apiUrl = Url::withDomainAndProtocol($apiInfo['contact']['url']);
        return <<<DOC
            <!DOCTYPE html>
            <html lang="ru">
            <head>
              <meta charset="utf-8" />
              <meta name="viewport" content="width=device-width, initial-scale=1" />
              <meta name="description" content="SwaggerIU" />
              <title>API документация</title>
              <link rel="stylesheet" href="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui.css" />
            </head>
            <body>
            <div id="swagger-ui"></div>
            <script src="https://unpkg.com/swagger-ui-dist@4.5.0/swagger-ui-bundle.js" crossorigin></script>
            <script>
              window.onload = () => {
                window.ui = SwaggerUIBundle({
                  url: "{$apiUrl}",
                  dom_id: '#swagger-ui',
                });
              };
            </script>
            </body>
            </html>
        DOC;
    }

    /**
     * Получаю основную информацию по настройке API
     *
     * @return array<string, mixed>
     */
    private function _getApiInfo(): array
    {
        $apiInfo = Env::getApiInfo();
        if (empty($apiInfo)) {
            $apiInfo = [];
        }
        $apiInfo += [
            'title' => 'Eggheads.Solutions Api',
            'description' => 'Eggheads.Solutions Api. Документ сформирован автоматически, онлайн <a href="https://github.com/swagger-api/swagger-codegen/tree/3.0.0#online-generators">генератор кода API</a>',
            'version' => '1',
            'contact' => [
                'email' => 'tune@eggheads.solutions',
                'url' => '/apiDocumentation.json',
            ],
        ];

        return $apiInfo;
    }
}
