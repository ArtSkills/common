<?php
declare(strict_types=1);

namespace ArtSkills\Http;

use Cake\Core\Configure;
use Psr\Http\Message\RequestInterface;
use Cake\Http\Client\Response;

/**
 * Класс для работы с прокси
 *
 * Настройка:
 * - Прокси берутся из базы данных
 * - Имя подключения можно указать в конфигурации с ключом proxyDBConfig (по-умолчанию default)
 * - Имя таблицы с прокси можно указать в конфигурации с ключом proxyTableName (по-умолчанию proxy_config)
 *
 * Метод получения конфига прокси находится в классе
 * @see ProxyList
 *
 * Использование:
 * Можно отключить использование прокси, добавив параметр конфигурации 'isProxyEnabled' => false
 * В этом случае будет работать как обычный \Eggheads\CakephpCommon\Http\Client
 *
 * При ошибке запроса Curl перечисленной в \Eggheads\CakephpCommon\Http\Client::REPEAT_REQUEST_CURL_ERROR,
 * происходит автоматическая смена прокси.
 * Данное поведение можно отключить вызвав метод
 * @see ProxyClient::doNotChangeProxyAfterError()
 *
 * Для смены прокси между запросами, можно вызвать метод
 * @see ProxyClient::changeProxy()
 */
class ProxyClient extends Client
{
    /** @var bool Менять ли прокси после ошибки curl */
    private bool $_isChangeProxyAfterError = true;

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    public function __construct(array $config = [])
    {
        $this->changeProxy();
        parent::__construct($config);
    }

    /**
     * Меняем прокси при ошибке запроса
     *
     * @inheritDoc
     * @phpstan-ignore-next-line
     */
    protected function _sendRequest(RequestInterface $request, $options, callable $errorCallback = null): Response
    {
        if ($this->_isChangeProxyAfterError) {
            return parent::_sendRequest($request, $options, function () {
                $this->changeProxy();
            });
        }
        return parent::_sendRequest($request, $options);
    }

    /**
     * Сменить прокси
     *
     * @return $this
     */
    public function changeProxy(): self
    {
        if (Configure::read('isProxyEnabled', true)) {
            $proxy = ProxyList::getInstance()->getConfig();
            if (!is_null($proxy)) {
                $this->setConfig('proxy', $proxy->toArray());
            }
        }
        return $this;
    }

    /**
     * Отключить смену прокси после ошибки
     *
     * @return $this
     */
    public function doNotChangeProxyAfterError(): self
    {
        $this->_isChangeProxyAfterError = false;
        return $this;
    }
}
