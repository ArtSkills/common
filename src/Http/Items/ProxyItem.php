<?php
declare(strict_types=1);

namespace ArtSkills\Http\Items;

use ArtSkills\ValueObject\ValueObject;

class ProxyItem extends ValueObject
{
    /** @var string Прокси вида ip:port */
    public string $proxy;

    /** @var string Имя пользователя */
    public string $username;

    /** @var string Пароль */
    public string $password;
}
