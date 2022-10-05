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
     * Полная инфа по текущему взаимодействию (запрос и ответ)
     *
     * @var array|null
     * @phpstan-ignore-next-line
     */
    private ?array $_currentRequestData = null;

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
     * @return array
     * @phpstan-ignore-next-line
     * @SuppressWarnings(PHPMD.MethodArgs)
     */
    public function send(Request $request, array $options)
    {
        $this->_currentRequestData = [
            'request' => $request,
            'response' => '',
        ];

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
     * @phpstan-ignore-next-line
     */
    public function createResponse($handle, $responseData)
    {
        $result = parent::createResponse($handle, $responseData);

        $this->_currentRequestData['response'] = end($result);

        HttpClientMocker::addSniff($this->_currentRequestData);
        $this->_currentRequestData = null;

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
