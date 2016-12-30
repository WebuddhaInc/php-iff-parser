<?PHP

spl_autoload_register(function ($class_name) {
  $class_name = strtolower($class_name);
  if (strpos($class_name, 'webuddhainc\\iif\\') === 0) {
    $class_keys = explode('\\', $class_name);
    array_shift($class_keys);array_shift($class_keys);
    require_once __DIR__ . '/IIF/' . str_replace(' ', '/', ucwords(implode(' ',$class_keys))) . '.php';
  }
});