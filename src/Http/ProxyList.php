<?php
declare(strict_types=1);

namespace ArtSkills\Http;

use ArtSkills\Http\Items\ProxyItem;
use Cake\Core\Configure;
use Cake\Datasource\ConnectionManager;
use ArtSkills\Traits\Singleton;

class ProxyList
{
    use Singleton;

    /** @var string Конфиг для подключения к базе данных по-умолчанию */
    private const DEFAULT_DB_CONFIG = 'default';

    /** @var string Таблица с прокси по-умолчанию */
    private const DEFAULT_TABLE_NAME = 'proxy_config';

    /**
     * Список текущих прокси
     *
     * @var ProxyItem[]|null
     */
    private ?array $_proxyList;

    /** @inheritDoc */
    private function __construct()
    {
        $this->_loadProxy();
    }

    /**
     * Получить случайный конфиг прокси
     *
     * @return ProxyItem|null
     */
    public function getConfig(): ?ProxyItem
    {
        if (empty($this->_proxyList)) {
            return null;
        }

        // Специально, дабы статический счётчик в другом процессе не работает, rand возвращает общее значение
        $maxIndex = count($this->_proxyList);
        $index = (int)ConnectionManager::get(Configure::read('proxyDBConfig', self::DEFAULT_DB_CONFIG))
                          ->query("SELECT FLOOR(RAND() * $maxIndex) AS random_value")
                          ->fetch('assoc')['random_value'];

        return $this->_proxyList[$index];
    }

    /**
     * Загружаем список прокси
     *
     * @return void
     */
    private function _loadProxy(): void
    {
        $configName = Configure::read('proxyDBConfig', self::DEFAULT_DB_CONFIG);
        $tableName = Configure::read('proxyTableName', self::DEFAULT_TABLE_NAME);
        $rows = ConnectionManager::get($configName)
            ->execute("SELECT proxy, username, password FROM $tableName WHERE active = 1")
            ->fetchAll('assoc');
        $this->_proxyList = array_map([ProxyItem::class, 'create'], $rows);
    }

    /**
     * Вернуть весь список прокси
     *
     * @return ProxyItem[]|null
     */
    public function getFullProxyList()
    {
        return $this->_proxyList;
    }
}
