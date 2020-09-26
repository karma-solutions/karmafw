

# App

```
class App
```


```
public static function boot()
# This method loads helpers files and init the SQL db connection.
```

```
public static function registerHelpersDir($dir)
# Add a directory to the helpers directories
```

```
public static function unregisterHelpersDir($dir)
# Remove a directory from the helpers directories
```

```
public static function loadHelpers($dir)
# Load all helpers files in a directory
```

[SQL db connection](Database/Sql/)
```
public static function getDb($instance_name=null, $dsn=null)
# Load a SQL db connection
```

```
public static function createOrmItem($table_name, $primary_key_values=[], $db=null)
```


# WebApp

```
class WebApp extends App
```


```
public static function boot()
# This method init the PHP session. It also calls App:boot()
```


[PHP/HTML template](Templates/)
```
public static function createTemplate($tpl_path=null, $variables=[], $layout=null, $templates_dirs=null)
# Create a PHP/HTML template object
```


```
public static function getUser()
# 
```

```
public static function setUser(array $user)
# 
```

```
public static function route
# [Routing](Routing/) : Parse the current url and calls the defined controller method regarding the routes configuration.
```

```
public static function error($http_status = 500, $meta_title = 'Server Error', $h1 = 'Error 500 - Server Error', $message = 'an error has occured')
# Display an error page
```

```
public static function error400($title = 'Bad request', $message = '')
# 
```

```
public static function error403($title = 'Forbidden', $message = 'You are not allowed')
# 
```

```
public static function error404($title = 'Page not Found', $message = "The page you're looking for doesn't exist")
# 
```

```
public static function error500($title = 'Internal Server Error', $message = 'An error has occured')
# 
```

```
public static function error503(title = 'Service Unavailable', $message = 'The service is unavailable')
# 
```
