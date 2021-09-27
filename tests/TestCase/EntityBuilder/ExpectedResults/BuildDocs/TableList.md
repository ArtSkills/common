## TestTableOne
description govno
### Поля:
* int `col_enum`
* \Cake\I18n\Time `col_time` = 'CURRENT_TIMESTAMP' asdasd
* int `id` comment1
* string `notExists`
* string `oldField`

## TestTableTwo
description qweqwe
### Поля:
* string `col_text` = NULL
* int `id`
* int `table_one_fk` blabla
* string `virtualField`
* string|null `virtualFieldOrNull`
### Связи:
* TestTableOne `$TestTableOne` TestTableOne.table_one_fk => TestTableTwo.id

