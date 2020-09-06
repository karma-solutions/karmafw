

# App

```
class App
```


```
boot()
# This method loads helpers files and init the SQL db connection.
```

```
registerHelpersDir($dir)
# Add a directory to the helpers directories
```

```
loadHelpers($dir)
# Load all helpers files in a directory
```

```
[getDb($instance_name=null, $dsn=null)](Database/Sql/)
# Load a SQL db connection
```

```
createOrmItem($table_name, $primary_key_values=[], $db=null)
```


# WebApp

```
class WebApp extends App
```


```
boot()
# This method init the PHP session. It also calls App:boot()
```


```
[createTemplate($tpl_path=null, $variables=[], $layout=null, $templates_dirs=null)](Templates/)
# Create a PHP/HTML template object
```


```
getUser
# 
```

```
setUser
# 
```

```
[route](Routing/)
# Parse the current url and calls the defined controller method regarding the routes configuration.
```

```
error
# Display an error page
```

```
error400
# 
```

```
error403
# 
```

```
error404
# 
```

```
error500
# 
```

```
error503
# 
```
