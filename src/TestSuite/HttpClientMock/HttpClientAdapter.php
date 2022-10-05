<?php
declare(strict_types=1);

namespace ArtSkills\TestSuite\HttpClientMock;

use ArtSkills\TestSuite\PermanentMocksCollection;
use Cake\Http\Client\Adapter\Curl;
use Cake\Http\Client\Request;
use Cake\Http\Client\Response;

/** @SuppressWarnings(PHPMD.MethodMix) */
class HttpClientAdapter extends Curl
{
    /**
     * Текущий запрос
     *
     * @var ?Request
     */
    private ?Request $_savedRequest = null;

    /**
     * Выводить ли информацию о незамоканных запросах
     *
     * @var bool
     */
    private static bool $_debugRequests = true;

    /**
     * Все запросы проверяются на подмену, а также логируются
     *
     * @param Request $request
     * @param array $options
     * @return Response[]
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function send(Request $request, array $options): array
    {
        $this->_savedRequest = $request;

        $mockData = HttpClientMocker::getMockedData($request);
        if ($mockData !== null) {
            return [new Response([
                'HTTP/1.1 ' . $mockData['status'],
                'Server: nginx/1.2.1',
            ], $mockData['response'])];
        } else {
            /** @var Response[] $result */
            $result = parent::send($request, $options);
            if (self::$_debugRequests) {
                PermanentMocksCollection::setHasWarning(true);
                PermanentMocksCollection::setWarningMessage('Вывод в консоль при запросе HTTP');
                file_put_contents('php://stderr', "==============================================================\n");
                file_put_contents('php://stderr', 'Do ' . $request->getMethod() . ' request to ' . $request->getUri() . ', Body: ' . $request->getBody() . "\n");
                file_put_contents('php://stderr', "Response: \n" . $result[0]->getStringBody() . "\n");
                file_put_contents('php://stderr', "==============================================================\n");
            }

            return $result;
        }
    }

    /**
     * @inheritdoc
     */
    public function createResponse($handle, $responseData)
    {
        $result = parent::createResponse($handle, $responseData);

        $response = $result[array_key_last($result)]; // @phpstan-ignore-line

        HttpClientMocker::addSniff([
            'request' => $this->_savedRequest,
            'response' => $response,
        ]);

        $this->_savedRequest = null;
        return $result;
    }

    /**
     * Включаем вывод запросов в консоль
     *
     * @return void
     */
    public static function enableDebug()
    {
        self::$_debugRequests = true;
    }

    /**
     * Выключаем вывод запросов в консоль
     *
     * @return void
     */
    public static function disableDebug()
    {
        self::$_debugRequests = false;
    }
}
