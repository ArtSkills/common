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
use OpenApi\Generator;

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
        $swagger = Generator::scan([__DIR__ . '/../Controller', $workDir]);
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
            if (!empty($objectDefinition['mergeProperties'])) {
                $mergeClassName = $objectDefinition['mergeProperties'][0];

                while (!empty($mergeClassName)) {
                    if (array_key_exists($mergeClassName, $objectDefinitions)) {
                        $objectDefinitions[$objectName]['properties'] += $objectDefinitions[$mergeClassName]['properties'];
                        // Проверяем наследуется ли родитель, если да продолжаем цикл
                        if (!empty($objectDefinitions[$mergeClassName]['mergeProperties'])) {
                            $mergeClassName = $objectDefinitions[$mergeClassName]['mergeProperties'][0];
                        } else {
                            unset($objectDefinitions[$objectName]['mergeProperties']);
                            $mergeClassName = null;
                        }
                    } else {
                        Log::error("Merge class $mergeClassName is not defined!");
                    }
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

        if (!empty($schema->allOf) && $schema->allOf !== Generator::UNDEFINED) { // наследование классов
            foreach ($schema->allOf as $subSchema) {
                if ($subSchema->ref === Generator::UNDEFINED) { // по логике либы это является конечным элементом наследования
                    if ($subSchema->properties !== Generator::UNDEFINED) {
                        foreach ($subSchema->properties as $property) {
                            $insProperty = $this->_getProperty($property);
                            $result['properties'][$insProperty['name']] = $insProperty;
                        }
                    }

                    if ($subSchema->type !== Generator::UNDEFINED) {
                        $result['type'] = $subSchema->type;
                    }
                    if ($subSchema->description !== Generator::UNDEFINED) {
                        $result['description'] = $subSchema->description;
                    }
                } else {
                    $result['mergeProperties'][] = $this->_getSchemaClassName($subSchema);
                }
            }
        } elseif (!empty($schema->_context)) {
            if ($schema->type !== Generator::UNDEFINED) {
                $result['type'] = $schema->type;
            }
            if ($schema->properties !== Generator::UNDEFINED) {
                $schema->type = 'object';
                foreach ($schema->properties as $property) {
                    $insProperty = $this->_getProperty($property);
                    $result['properties'][$insProperty['name']] = $insProperty;
                }
            } elseif ($schema->type === 'array') { // @phpstan-ignore-line ошибочное умозаключение
                $result['type'] = $schema->items->ref . '[]';
            }

            if ($schema->description !== Generator::UNDEFINED) {
                $result['description'] = $schema->description;
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
            'description' => $property->description !== Generator::UNDEFINED ? $property->description : null,
            'type' => $property->type,
        ];
        if ($property->type === 'array') {
            if ($property->items->ref !== Generator::UNDEFINED) {
                $result['type'] = str_replace(self::SCHEMA_PATH_PREFIX, '', $property->items->ref) . '[]';
            } elseif ($property->items->type !== Generator::UNDEFINED) {
                $result['type'] = $property->items->type . '[]';
            } else {
                Log::error("Incorrect property type for " . $property->_context->namespace . '\\' . $property->_context->class . '::' . $property->property);
            }
        } elseif ($property->ref !== Generator::UNDEFINED) {
            $result['type'] = str_replace(self::SCHEMA_PATH_PREFIX, '', $property->ref);
        } elseif ($property->oneOf !== Generator::UNDEFINED && !empty($property->oneOf[0]) && $property->oneOf[0]->ref !== Generator::UNDEFINED) {
            $result['type'] = str_replace(self::SCHEMA_PATH_PREFIX, '', $property->oneOf[0]->ref);
        } elseif ($property->type === Generator::UNDEFINED) {
            Log::error("Incorrect property type for " . $property->_context->namespace . '\\' . $property->_context->class . '::' . $property->property);
        }

        if ($property->enum !== Generator::UNDEFINED) {
            $result['description'] = (!empty($result['description']) ? Strings::replaceIfEndsWith($result['description'], '.', '') . '. ' : '') . 'Возможные значения: ' . implode(', ', $property->enum);
        }

        if ($property->nullable === true) {
            $result['type'] .= '|null';
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
        if ($schema->schema !== Generator::UNDEFINED) {
            return $schema->schema;
        }
        if ($schema->ref !== Generator::UNDEFINED) {
            return str_replace(self::SCHEMA_PATH_PREFIX, '', $schema->ref);
        }

        if (!empty($schema->_context)) {
            return $schema->_context->class;
        } elseif (!empty($schema->allOf)) {
            foreach ($schema->allOf as $subSchema) {
                if ($subSchema->ref === Generator::UNDEFINED) { // конечный класс наследования
                    return $subSchema->_context->class;
                }
            }
            throw new UserException("Unknown format(1) schema " . $schema->schema);
        } else {
            throw new UserException("Unknown format(2) schema " . $schema->schema);
        }
    }
}
