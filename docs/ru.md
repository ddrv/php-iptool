# 1. Установка

## 1.1. Системные требования
* PHP 5.3 или выше
* PHP Расширение PDO
* PHP Расширение PDO SQLite

## 1.2. Установка с помощью composer
1. Добавьте пакет в зависимости:
    ```text
    php composer.phar require ddrv/iptool
    ```
2. Подключите автозагрузку классов
    ```php
    include('vendor/autoload.php');
    ```
## Ручная установка
1. Скачайте [архив](https://github.com/ddrv/iptool/archive/master.zip)
2. Распакуйте в директорию с библиотеками проекта /path/to/libraries/
3. Подключите файлы
    ```php
    /* Если вам нужно создать БД */
    require_once('/path/to/libraries/iptool/src/Converter.php');
    /* Если нужно искать IP адреса */
    require_once('/path/to/libraries/iptool/src/Iptool.php');
    ```

# 2. Использование

## 2.1. Инициализация IP Tool
```php
/* Путь к базе данных - /path/to/iptool.database */
$iptool = new \Ddrv\Iptool\Iptool('/path/to/iptool.database');
```

## 2.2. Получение информации о базе данных
```php
print_r($iptool->about());
```
```text
Array
(
    [created] => 1507199627
    [author] => Ivan Dudarev
    [license] => MIT
    [networks] => Array
        (
            [count] => 276148
            [data] => Array
                (
                    [country] => Array
                        (
                            [0] => code
                            [1] => name
                        )
                )
        )
)
```
## 2.3. Поиск информации об IP адресе
```php
print_r($iptool->find('81.32.17.89'));
```
```text
Array
(
    [network] => Array
        (
            [0] => 81.32.0.0
            [1] => 81.48.0.0
        )
    [data] => Array
        (
            [country] => Array
                (
                    [code] => es
                    [name] => Spain
                )
        )
)
```

## 2.4. Получить все элементы справочника
```php
print_r($iptool->getRegister('country'));
```
```text
Array
(
    [1] => Array
        (
            [code] => cn
            [name] => China
        )
    [2] => Array
        (
            [code] => es
            [name] => Spain
        )
...
    [N] => Array
        (
            [code] => jp
            [name] => Japan
        )
)
```

## 2.5. Получение элемента справочника по его порядковому номеру
```php
print_r($iptool->getRegister('country',2));
```
```text
Array
    (
        [code] => cn
        [name] => China
    )
)
```
## 2.6. Создание базы данных

Для создания базы данных используйте класс Converter.
1. Подготовьте файлы, описывающие интервалы адресов и справочники. Эти файлы должны выглядеть как таблицы реляционной базы данных.
    ###### Пример
    Интервалы адресов `/path/to/cvs/networks.csv`
    ```text
    first_ip,last_ip,register_id
    "0.0.0.0","63.255.255.255",89
    "64.0.0.0","127.255.255.255",192
    "128.0.0.0","191.255.255.255",2
    "192.0.0.0","255.255.255.255",17
    ```
    Файл справочника `/path/to/cvs/info.csv`.
    ```text
    Строка с какой-либо информацией (например, копирайт). Вторая строка описывает колонки и тоже не будет использоваться в БД.
    id,interval_num,data1,data2
    2,3,"some info 1","some info 2"
    17,4,"some info 3","some info 4"
    89,1,"some info 5","some info 6"
    192,2,"some info 7","some info 8"
    34,"unused row","some info 9","some info 10"
    ```
1. Инициализируйте конвертер.
    ```php
    $tmpDir = 'path/to/dir/for/temporary/files';
    $converter = new \Ddrv\Iptool\Converter($tmpDir);
    ```
1. Укажите информацию об авторе БД.
    ```php
    /**
     * $author - строка длиной не более 64 символов.
     */
    $author = 'Name Surname';
    $converter->setAuthor($author);
    ```
1. Укажите время создания БД.
    ```php
    /**
     * $time - время в формате unixstamp.
     */
    $converter->setTime(1507638600); // 2017/10/10 15:30:00
    ```

1. Укажите лицензию базы данных.
    ```php
    /**
     * $license - может быть название публичной лицензии, ссылка на лицензионное соглашение или же непосредственно текст лицензии. Длина не лимитирована.
     */
    $license = 'MIT';
    $converter->setLicense($license);
    ```

1. Добавьте подготовленные файлы. Воспользуйтесь методом addCSV() с параметрами:
    * уникальный идентификатор для файла. Обязательный параметр;
    * путь к  CSV файлу. Обязательный параметр;
    * количество игнорируемых строк с начала файла (по умолчанию 0);
    * кодировка CSV файла (по умолчанию UTF-8);
    * разделитель колонок в CSV (по умолчанию ,);
    * символ, обрамляющий значение колонки CSV (по умолчанию ");
    * экранирующий символ CSV (по умолчанию \).
    ```php
    $converter->addCSV('infoCSV','/path/to/cvs/info.csv',2);
    $converter->addCSV('networksCSV','/path/to/cvs/networks.csv',1);
    ```

1. Опишите формат справочника.
    ```php
    $info = array(
        'interval' => array(
            /**
             * Тип может быть:
             * small - целое число от -128 до 127;
             * int - целое число;
             * long - большое целое число;
             * float - число с плавающей точкой;
             * double - дробное число (удобно использовать для храннения координат);
             * string - строка.
             */
            'type' => 'int',
            /**
             * Номер колонки, содержащей значение параметра. Счёт колонок начинается с 0
             */
            'column' => 1,
        ),
        'caption' => array(
            'type' => 'string',
            'column' => 2,
            /**
             * Для типа string можно добавить параметр transform. Он может быть:
             * low - преобразует строку в нижний регистр;
             * up - преобразует строку в верхний регистр;
             * none - Оставляет строку как есть. Используется по умолчанию.
             */
            'transform' => 'low',
        ),
        'extendedInfo' => array(
            'type' => 'string',
            'column' => 3,
        ),
    );
    ```
1. Добавьте определённый справочник. Воспользуйтесь методом addRegister() с параметрами:
    * название регистра. Обязательный параметр;
    * уникальная строка, идентифицирующая CSV файл (вы её определили при использовании метода addCSV). Обязательный параметр;
    * номер колонки, содержащей идентификатор строки. Счёт колонок начинается с 0;
    * описанный формат справочника.
    ```php
    $converter->addRegister('info','infoCSV',0, $info);
    ```
1. Определите формат интервалов адресов.
    ```php
    $networks = array(
        /**
         * Ключ - название справочника;
         * Значение - номер колонки с идентификатором строки справочника.
         */
        'info' => 2,
    );
    ```

1. Добавьте интервалы адресов. Вызовите метод addNetworks() с параметрами:
    * уникальная строка, идентифицирующая CSV файл (вы её определили при использовании метода addCSV). Обязательный параметр;
    * формат IP адреса в CSV файле. Может быть:
        * ip (если IP представлены в обычном формате. Например, 123.123.123.123);
        * long (IP представлен в виде числа. Например, 1361051648);
        * inetnum (IP представлен в виде маски или интервала. Например, 1.0.0.0/24 или 1.0.0.1-1.0.0.255).
    * колонка с начальным адресом диапазона. Счёт колонок начинается с 0;
    * колонка с последний адресом диапазона. Счёт колонок начинаетсяс 0;
    * формат интервалов.
    ```php
    $converter->addNetworks('networksCSV', 'ip', 0, 1, $networks);
    ```
1. Запустите компиляцию БД
    ```php
    $errors = $converter->getErrors();
    if (!$errors) {
        $dbFile = 'path/to/created/database.file';
        $converter->create($dbFile);
    } else {
        print_r($errors);
    }
    ```
    Дождитесь окончания компиляции и используйте!

# 3. Формат базы данных

|Размер|Описание|
|---|---|
|3|Контрольное слово для проверки принадлености файла к библиотеке. Всегда равно DIT|
|1|Формат unpak для чтения размера заголовка|
|1 или 4|Размер заголовка (L)|
|1|Версия формата Iptool|
|1|Количество справочников (RC)|
|4|Размер формата unpack описания справочников (RF)|
|RF|Формат unpack описания справочников|
|RF*(RC+1)|Описания справочников|
|1024|Индекс первых октетов|
|?|БД диапазонов|
|?|БД справочника 1|
|?|БД справочника 2|
|...|...|
|?|БД справочника RC|
|4|Время создания БД в формате Unixstamp|
|128|Автор БД|
|?|Лицензия БД|

# 4. Примеры
## 4.1. Создание БД используя данные GeoLite2 Country
```php
<?php
/* Используем директорию для хранения временных файлов. У скрипта должны быть права на запись в эту директорию. */
$tmpDir = __DIR__.DIRECTORY_SEPARATOR.'tmp';

/* Инициализируем класс Converter. */
$converter = new \Ddrv\Iptool\Converter($tmpDir);

/* Указываем путь для сохранения БД. Скрипт должен иметь права на запись этого файла. */
$dbFile = __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'iptool.geo.country.dat';

/* УРЛ для скачивания архива.*/
$url = 'http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country-CSV.zip';

/* Имя временного файла. */
$tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'geolite2country.zip';

/* Скачиваем архив. */
copy($url,$tmpFile);

/* Ищем в архиве путь к нужным файлам. */
$zip = new ZipArchive();
if ($zip->open($tmpFile) !== true) die;
$i = -1;
$zipPath = null;
do {
    $i++;
    $csv = $zip->getNameIndex($i);
    preg_match('/(?<file>(?<zipPath>.*)\/GeoLite2\-Country\-Blocks\-IPv4\.csv)$/ui', $csv, $m);
} while ($i < $zip->numFiles && empty($m['file']));
$zipPath = $m['zipPath'];
$zip->close();

/* Запоминаем в переменные пути к нужным CSV файлам. */
$locations = 'zip://' . $tmpFile . '#'.$zipPath.DIRECTORY_SEPARATOR.'GeoLite2-Country-Locations-en.csv';
$networks = 'zip://' . $tmpFile . '#' . $m['file'];

/* Устанавливаем инфорацию об авторе. */
$converter->setAuthor('Ivan Dudarev');

/* Указываем лицензию. */
$converter->setLicense('MIT');

/* Добавляем исходники в формате CSV. */
$converter->addCSV('locations',$locations,1);
$converter->addCSV('networks',$networks,1);

/* Описываем справочник Geo. */
$country = array(
    'code' => array(
        'type' => 'string',
        'column' => 4,
        'transform' => 'low',
    ),
    'name' => array(
        'type' => 'string',
        'column' => 5,
    ),
);
$converter->addRegister('country','locations',0, $country);

/* Описываем диапазоны. */
$data = array(
     'country' => 1,
);
$converter->addNetworks('networks', 'inetnum', 0, 0, $data);

/* Компилируем БД. */
$converter->create($dbFile);

/* Удаляем временный файл */
unlink($tmpFile);

/* Получаем информацию о созданной БД */
$iptool = new \Ddrv\Iptool\Iptool($dbFile);
print_r($iptool->about());

/* Ищем в БД информацию по адресу */
print_r($iptool->find('95.215.84.0'));
```
## 4.2. Создание БД используя данные GeoLite2 City
```php
<?php
/* Используем директорию для хранения временных файлов. У скрипта должны быть права на запись в эту директорию. */
$tmpDir = __DIR__.DIRECTORY_SEPARATOR.'tmp';

/* Инициализируем класс Converter. */
$converter = new \Ddrv\Iptool\Converter($tmpDir);

/* Указываем путь для сохранения БД. Скрипт должен иметь права на запись этого файла. */
$dbFile = __DIR__.DIRECTORY_SEPARATOR.'data'.DIRECTORY_SEPARATOR.'iptool.geo.city.dat';

/* УРЛ для скачивания архива.*/
$url = 'http://geolite.maxmind.com/download/geoip/database/GeoLiteCity_CSV/GeoLiteCity-latest.zip';

/* Имя временного файла. */
$tmpFile = $tmpDir . DIRECTORY_SEPARATOR . 'geolite2city.zip';

/* Скачиваем архив. */
copy($url,$tmpFile);

/* Ищем в архиве путь к нужным файлам. */
$zip = new ZipArchive();
if ($zip->open($tmpFile) !== true) die;
$i = -1;
$zipPath = null;
do {
    $i++;
    $csv = $zip->getNameIndex($i);
    preg_match('/(?<file>(?<zipPath>.*)\/GeoLiteCity\-Blocks\.csv)$/ui', $csv, $m);
} while ($i < $zip->numFiles && empty($m['file']));
$zipPath = $m['zipPath'];
$zip->close();

/* Запоминаем в переменные пути к нужным CSV файлам. */
$locations = 'zip://' . $tmpFile . '#'.$zipPath.DIRECTORY_SEPARATOR.'GeoLiteCity-Location.csv';
$networks = 'zip://' . $tmpFile . '#' . $m['file'];

/* Устанавливаем инфорацию об авторе. */
$converter->setAuthor('Ivan Dudarev');

/* Указываем лицензию. */
$converter->setLicense('MIT');

/* Добавляем исходники в формате CSV. */
$converter->addCSV('locations',$locations,2);
$converter->addCSV('networks',$networks,2);

/* Описываем справочник Geo. */
$geo = array(
    'geonames' => array(
        'type' => 'int',
        'column' => 0,
    ),
    'country' => array(
        'type' => 'string',
        'column' => 1,
        'transform' => 'low',
    ),
    'region' => array(
        'type' => 'string',
        'column' => 2,
    ),
    'city' => array(
        'type' => 'string',
        'column' => 3,
    ),
    'latitude' => array(
        'type' => 'double',
        'column' => 5,
    ),
    'longitude' => array(
        'type' => 'double',
        'column' => 6,
    ),
);
$converter->addRegister('geo','locations',0, $geo);

/* Описываем диапазоны. */
$data = array(
     'geo' => 2,
);
$converter->addNetworks('networks', 'long', 0, 1, $data);

/* Компилируем БД. */
$converter->create($dbFile);

/* Удаляем временный файл */
unlink($tmpFile);

/* Получаем информацию о созданной БД */
$iptool = new \Ddrv\Iptool\Iptool($dbFile);
print_r($iptool->about());

/* Ищем в БД информацию по адресу */
print_r($iptool->find('95.215.84.0'));
```
