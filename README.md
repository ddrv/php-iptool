# IP Tool
Define data by IP Address

# Install
Add to `composer.json`:
```json
{
    "require": {
        "ddrv/iptool":"*"
    }
}
```
And run
```text
php composer.phar install
```

# Create your IP addresses database
See examples for create database:
* Geo from Maxmind GeoLite2 Country `examples/geoFromMaxmindCountry.php`
* Geo from Maxmind GeoLite2 City `examples/geoFromMaxmindCity.php`

# Use
```php
/**
 * Initialization IP Tool
 */
$iptool = new \Ddrv\Iptool\Iptool('/path/to/iptool.database');
```

```php
/**
 * Get information about created database
 */
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
```php
/**
 * Search IP Address data
 */
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