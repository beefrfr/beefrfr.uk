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

 ## Creating pages
 Pages can be created by navigating to `/admin/pages` and using the page editor there.

 Advanced pages can be produced by writing either HTML or PHP code in the `/config/` folder and then navigating to this page in a browser. For example, in `/custom/` create a file `example.php`, to access this file go to `<yoururl>/example.php`.

For `.php` files, there should be *no output* in the file, but instead modify/create an array `$page`. For example:
```
$page = Array(
    "tabTitle" => "Tab Title",
    "pageTitle" => "Page Title",
    "content" => "Content"
);
```

To include HTML boilerplate in your code use the [`templateHandler`](libraries/templateHandler.php) class. For example:
```
$template = new TemplateHandler('/custom/templates/mytemplate.html');
$page = Array(
    "tabTitle" => "Blog",
    "pageTitle" => "Page Title",
    "content" => $template->prepare([
        ["key" => '$username', "value" => "username"]
    ]);
);
```