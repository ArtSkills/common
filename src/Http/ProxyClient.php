<?php
declare(strict_types=1);

namespace ArtSkills\Http;

use ArtSkills\Error\InternalException;
use Cake\Core\Configure;

/**
 * Класс для работы с прокси
 *
 * Использование:
 * Можно отключить использование прокси, добавив параметр конфигурации 'isProxyEnabled' => false
 * В этом случае будет работать как обычный \Eggheads\CakephpCommon\Http\Client
 */
class ProxyClient extends Client
{
    /** @var string Элемент массива с параметрами в общем конфиге */
    public const CONFIG_FIELD_NAME = 'proxyConfig';

    /**
     * @inheritDoc
     * @phpstan-ignore-next-line
     * @throws InternalException
     */
    public function __construct(array $config = [])
    {
        if (Configure::read('isProxyEnabled', true)) {
            $this->setConfig('proxy', $this->_getConfig());
        }

        parent::__construct($config);
    }

    /**
     * Получение и проверка конфигурации
     *
     * @return array{proxy: string, username: string, password: string}
     * @throws InternalException
     */
    private function _getConfig(): array
    {
        $proxyConfig = Configure::read(self::CONFIG_FIELD_NAME);
        if (empty($proxyConfig)) {
            throw new InternalException(self::CONFIG_FIELD_NAME . ' отсутствует в конфигурации');
        }

        if (!is_array($proxyConfig) ||
            empty($proxyConfig['proxy']) || !is_string($proxyConfig['proxy']) ||
            empty($proxyConfig['username']) || !is_string($proxyConfig['username']) ||
            empty($proxyConfig['password']) || !is_string($proxyConfig['password'])
        ) {
            throw new InternalException('Невалидная конфигурация ' . self::CONFIG_FIELD_NAME);
        }

        return $proxyConfig;
    }
}
