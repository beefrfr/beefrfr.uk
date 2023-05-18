# Code for [beefrfr](https://beefrfr.uk)

# Setup
## Composer
**Make sure [composer](https://github.com/composer/composer) is [installed](https://getcomposer.org/download/)**
1. `composer requires phpmailer/phpmailer`
2. `composer requires erusev/parsedown`
3. `composer requires symfony/yaml`
4. `composer update`

## Config
Ensure the following files and keys exist:
1. `/config/db.yml`:
 - `database_host: %s`
 - `database_username: %s`
 - `database_password: %s`
 - `database_name: %s`

