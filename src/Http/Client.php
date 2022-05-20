<?php
declare(strict_types=1);

namespace ArtSkills\Http;

use ArtSkills\Lib\Arrays;
use Cake\Http\Client\Request;
use Cake\Http\Client\Response;
use Cake\Http\Exception\HttpException;
use ArtSkills\Lib\Env;

class Client extends \Cake\Http\Client
{
    /** @var int Таймаут перед повторным подключением по-умолчанию (сек) */
    public const DEFAULT_REPEAT_REQUEST_TIMEOUT = 10;

    /** @var int Таймаут запроса по-умолчанию (сек) */
    public const DEFAULT_TIMEOUT = 30;

    /** @var string[] Заголовки по-умолчанию */
    public const DEFAULT_HEADERS = [
        'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_13_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/75.0.3770.100 Safari/537.36',
    ];

    /** @var string Тип контента - json */
    public const CONTENT_TYPE_JSON = 'application/json';

    /** @var string[] Заголовки по-умолчанию только для POST запросов */
    public const DEFAULT_POST_HEADERS = [
        'Content-Type' => self::CONTENT_TYPE_JSON,
    ];

    /** @var int Кол-в редиректов при запросе по-умолчанию */
    public const DEFAULT_REDIRECT_COUNT = 2;

    /** @var int[] Перечень ошибок CURL при, которых делается попытка повторного запроса */
    private const REPEAT_REQUEST_CURL_ERROR = [6, 7, 18, 28, 35, 55, 56];

    /** @var int Таймаут перед повторным подключением */
    private int $_repeatRequestTimeout = self::DEFAULT_REPEAT_REQUEST_TIMEOUT;

    /** @var bool Повторять ли запрос при неудаче */
    private bool $_isRepeatRequest = true;

    /**
     * Client constructor.
     *
     * @param array $config
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    public function __construct(array $config = [])
    {
        if (!array_key_exists('redirect', $config)) {
            $config['redirect'] = self::DEFAULT_REDIRECT_COUNT;
        }

        if (!array_key_exists('timeout', $config)) {
            $config['timeout'] = self::DEFAULT_TIMEOUT;
        }

        // Возможность глобального переопределения адаптера отправки запросов
        if (Env::hasHttpClientAdapter()) {
            $config['adapter'] = Env::getHttpClientAdapter();
        }

        parent::__construct($config);
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    protected function _doRequest($method, $url, $data, $options): Response
    {
        // Добавляем заголовков по-умолчанию
        $options['headers'] = ($options['headers'] ?? []) + self::DEFAULT_HEADERS;

        return parent::_doRequest($method, $url, $data, $options);
    }

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function post($url, $data = [], array $options = []): Response
    {
        // Добавляем заголовков по-умолчанию для post запросов
        $options['headers'] = ($options['headers'] ?? []) + self::DEFAULT_POST_HEADERS;

        // Если 'Content-Type' => 'application/json', то $data должен быть json-строкой
        if ($options['headers']['Content-Type'] === self::CONTENT_TYPE_JSON && is_array($data)) {
            $data = Arrays::encode($data);
        }
        return parent::post($url, $data, $options);
    }

    /**
     * Дважды отправляем запрос при таймауте
     *
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    protected function _sendRequest(Request $request, $options, callable $errorCallback = null): Response
    {
        try {
            $result = parent::_sendRequest($request, $options);
        } catch (HttpException $exception) {
            if ($this->_isRepeatRequest && in_array($this->_getCurlErrorCode($exception->getMessage()), self::REPEAT_REQUEST_CURL_ERROR)) {
                if (is_callable($errorCallback)) {
                    $errorCallback($exception);
                }
                sleep($this->_repeatRequestTimeout);
                $result = parent::_sendRequest($request, $options);
            } else {
                throw $exception;
            }
        }
        return $result;
    }

    /**
     * Установить таймаут перед повторным запросе
     *
     * @param int $timeout
     * @return $this
     */
    public function setRepeatRequestTimeout(int $timeout): self
    {
        $this->_repeatRequestTimeout = $timeout;
        return $this;
    }

    /**
     * Отключить повторение запроса при неудаче
     *
     * @return $this
     */
    public function doNotRepeatRequest(): self
    {
        $this->_isRepeatRequest = false;
        return $this;
    }

    /**
     * Извлекаем код ошибки CURL из сообщения об ошибке
     *
     * @param string $errorMessage
     * @return int
     */
    private function _getCurlErrorCode(string $errorMessage): int
    {
        $re = '/cURL Error \((\d+)\)/mi';
        preg_match_all($re, $errorMessage, $matches, PREG_SET_ORDER);
        return (int)($matches[0][1] ?? 0);
    }
}
