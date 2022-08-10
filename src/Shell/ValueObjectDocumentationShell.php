<?php
declare(strict_types=1);

namespace ArtSkills\Shell;

use ArtSkills\Lib\Strings;
use ArtSkills\Error\UserException;
use Cake\Console\Shell;
use Cake\Filesystem\File;
use Cake\Log\Log;
use OpenApi\Annotations\Property;
use OpenApi\Annotations\Schema;
use function OpenApi\scan;
use const OpenApi\UNDEFINED;

class ValueObjectDocumentationShell extends Shell
{
    protected const SCHEMA_PATH_PREFIX = '#/components/schemas/';

    /**
     * Генератор JS документации для ValueObject
     *
     * @param string $workDir Папка, которую нужно парсить
     * @param string|null $resultFilePath В какой файл писать результат
     * @return void
     */
    public function main(string $workDir = APP, ?string $resultFilePath = null)
    {
        $swagger = scan([$workDir, __DIR__ . '/../Controller']);

        $objectDefinitions = [];
        foreach ($swagger->components->schemas as $index => $schema) {
            try {
                $def = $this->_getDefinition($schema);
                if (array_key_exists($def['name'], $objectDefinitions)) {
                    Log::error($def['name'] . ' object is already defined!');
                } else {
                    $objectDefinitions[$def['name']] = $def;
                }
            } catch (UserException $exception) {
                Log::error($index . ": " . $exception->getMessage());
            }
        }

        $objectDefinitions = $this->_mergeInheritanceProperties($objectDefinitions);

        $resultFileContents = "/** Autogenerated content, use ValueObjectDocumentationShell to update this file! */\n\n";
        foreach ($objectDefinitions as $objectDefinition) {
            $resultFileContents .= $this->_makeObjectDefinitionString($objectDefinition);
        }

        if (empty($resultFilePath)) {
            $resultFile = new File(WWW_ROOT . 'js' . DS . 'valueObjects.js');
        } else {
            $resultFile = new File($resultFilePath);
        }

        if (!$resultFile->exists() || md5($resultFile->read()) !== md5($resultFileContents)) {
            $resultFile->write($resultFileContents);
            $this->out("You has new data, sync " . $resultFile->path . " from server");
        }
    }

    /**
     * Формируем typeDef строку
     *
     * @param array $objectDefinition
     * @return string
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    private function _makeObjectDefinitionString(array $objectDefinition): string
    {
        $result = "/**\n * @typedef {" . $this->_getJsTypeName($objectDefinition['type']) . "} " . $objectDefinition['name'] . (!empty($objectDefinition['description']) ? ' ' . $objectDefinition['description'] : '') . "\n";
        foreach ($objectDefinition['properties'] as $property) {
            $result .= " * @prop {" . $this->_getJsTypeName($property['type']) . "} " . $property['name'] . (!empty($property['description']) ? ' ' . $property['description'] : '') . "\n";
        }
        $result .= " */\n\n";
        return $result;
    }

    /**
     * Формируем имя типа JS
     *
     * @param string $inType
     * @return string
     */
    private function _getJsTypeName(string $inType): string
    {
        switch ($inType) {
            case 'object':
                return 'Object';

            case 'integer':
                return 'number';

            default:
                return $inType;
        }
    }

    /**
     * Объединяем наследование
     *
     * @param array $objectDefinitions
     * @return array
     * @SuppressWarnings(PHPMD.MethodArgs)
     * @phpstan-ignore-next-line
     */
    private function _mergeInheritanceProperties(array $objectDefinitions): array
    {
        foreach ($objectDefinitions as $objectName => $objectDefinition) {
            foreach ($objectDefinition['mergeProperties'] as $classIndex => $mergeClassName) {
                if (array_key_exists($mergeClassName, $objectDefinitions)) {
                    $objectDefinitions[$objectName]['properties'] += $objectDefinitions[$mergeClassName]['properties'];
                    unset($objectDefinitions[$objectName]['mergeProperties'][$classIndex]);
                } else {
                    unset($objectDefinitions[$objectName]['mergeProperties'][$classIndex]);
                    Log::error("Merge class $mergeClassName is not defined!");
                }
            }
        }
        return $objectDefinitions;
    }

    /**
     * Определяем имя класса
     *
     * @param Schema $schema
     * @return array<string, array<array<string>|string>|string|null>
     * @throws UserException
     */
    private function _getDefinition(Schema $schema): array
    {
        $result = [
            'name' => $this->_getSchemaClassName($schema),
            'type' => 'object',
            'description' => null,
            'properties' => [],
            'mergeProperties' => [],
        ];

        if (!empty($schema->_context)) {
            if ($schema->type !== UNDEFINED) {
                $result['type'] = $schema->type;
            }
            if ($schema->properties !== UNDEFINED) {
                $schema->type = 'object';
                foreach ($schema->properties as $property) {
                    $insProperty = $this->_getProperty($property);
                    $result['properties'][$insProperty['name']] = $insProperty;
                }
            } elseif ($schema->type === 'array') { // @phpstan-ignore-line ошибочное умозаключение
                $result['type'] = $schema->items->ref . '[]';
            }

            if ($schema->description !== UNDEFINED) {
                $result['description'] = $schema->description;
            }
        } elseif (!empty($schema->allOf)) { // наследование классов
            foreach ($schema->allOf as $subSchema) {
                if ($subSchema->ref === UNDEFINED) { // по логике либы это является конечным элементом наследования
                    if ($subSchema->properties !== UNDEFINED) {
                        foreach ($subSchema->properties as $property) {
                            $insProperty = $this->_getProperty($property);
                            $result['properties'][$insProperty['name']] = $insProperty;
                        }
                    }

                    if ($subSchema->type !== UNDEFINED) {
                        $result['type'] = $subSchema->type;
                    }
                    if ($subSchema->description !== UNDEFINED) {
                        $result['description'] = $subSchema->description;
                    }
                } else {
                    $result['mergeProperties'][] = $this->_getSchemaClassName($subSchema);
                }
            }
        } else {
            throw new UserException("Unknown format schema(3) " . $schema->schema);
        }

        return $result;
    }

    /**
     * Получаем описание свойства
     *
     * @param Property $property
     * @return array{name: string, description: ?string, type: string}
     */
    private function _getProperty(Property $property): array
    {
        $result = [
            'name' => $property->property,
            'description' => $property->description !== UNDEFINED ? $property->description : null,
            'type' => $property->type,
        ];
        if ($property->type === 'array') {
            if ($property->items->ref !== UNDEFINED) {
                $result['type'] = str_replace(self::SCHEMA_PATH_PREFIX, '', $property->items->ref) . '[]';
            } else {
                $result['type'] = $property->items->type . '[]';
            }
        } elseif ($property->ref !== UNDEFINED) {
            $result['type'] = str_replace(self::SCHEMA_PATH_PREFIX, '', $property->ref);
        } elseif ($property->oneOf !== UNDEFINED && !empty($property->oneOf[0]) && $property->oneOf[0]->ref !== UNDEFINED) {
            $result['type'] = str_replace(self::SCHEMA_PATH_PREFIX, '', $property->oneOf[0]->ref);
        } elseif ($property->type === UNDEFINED) {
            Log::error("Incorrect property type for " . $property->_context->namespace . '\\' . $property->_context->class . '::' . $property->property);
        }

        if ($property->enum !== UNDEFINED) {
            $result['description'] = (!empty($result['description']) ? Strings::replaceIfEndsWith($result['description'], '.', '') . '. ' : '') . 'Возможные значения: ' . implode(', ', $property->enum);
        }
        return $result;
    }

    /**
     * Определяем имя класса для схемы
     *
     * @param Schema $schema
     * @return string
     * @throws UserException
     */
    private function _getSchemaClassName(Schema $schema): string
    {
        if ($schema->schema !== UNDEFINED) {
            return $schema->schema;
        }
        if ($schema->ref !== UNDEFINED) {
            return str_replace(self::SCHEMA_PATH_PREFIX, '', $schema->ref);
        }

        if (!empty($schema->_context)) {
            return $schema->_context->class;
        } elseif (!empty($schema->allOf)) {
            foreach ($schema->allOf as $subSchema) {
                if ($subSchema->ref === UNDEFINED) { // конечный класс наследования
                    return $subSchema->_context->class;
                }
            }
            throw new UserException("Unknown format(1) schema " . $schema->schema);
        } else {
            throw new UserException("Unknown format(2) schema " . $schema->schema);
        }
    }
}
