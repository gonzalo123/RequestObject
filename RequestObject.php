<?php
/**
 * THIS PROGRAM COMES WITH ABSOLUTELY NO WARANTIES !
 * USE IT AT YOUR OWN RISKS !
 *
 * @author Gonzalo Ayuso <gonzalo123@gmail.com>
 *
 * @copyright under GPL 2 licence
 */
class RequestObjectException extends Exception
{
    private $validationErrors = array();
    function __construct($message, $validationErrors)
    {
        $this->validationErrors = $validationErrors;
        parent::__construct($message);
    }

    public function getValidationErrors()
    {
        return $this->validationErrors;
    }
}

abstract class RequestObject
{
    private $parameters = array();
    private $reflection;

    private function processPHPDoc(ReflectionProperty $reflect)
    {
        $out = array('default' => null, 'cast' => 'raw');
        $docComment = $reflect->getDocComment();
        if (trim($docComment) == '') return $out;
        
        $docComment = preg_replace('#[ \t]*(?:\/\*\*|\*\/|\*)?[ ]{0,1}(.*)?#', '$1', $docComment);
        $docComment = trim(str_replace('*/', null, $docComment));

        foreach (explode("\n", $docComment) as $item) {
            $item = strtolower($item);
            if (strpos($item, '@cast') !== false) {
                $cast = trim(str_replace('@cast', null, $item));
                $out['cast'] = $cast;
            }

            if (strpos($item, '@default') !== false) {
                $default = trim(str_replace('@default', null, $item));
                $out['default'] = $default;
            }
        }
        return $out;
    }

    /**
     * @static
     * @param bool $validateInConstructor
     * @return RequestObject
     */
    public static function factory($validateInConstructor = true)
    {
        return new self($validateInConstructor);
    }

    /**
     * @var RequestObject
     */
    private static $instance = null;

    /**
     * @static
     * @return RequestObject
     */
    public static function singleton()
    {
        if (is_null(self::$instance)) {
            self::$instance = self::factory();
        }
        return self::$instance;
    }

    public function __construct($validateInConstructor = true)
    {
        $this->reflection = new ReflectionClass($this);

        $args = array();
        $availablefilters = $this->getAvailableFilters();

        foreach ($this->reflection->getProperties(ReflectionProperty::IS_PUBLIC) as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            $doc = $this->processPHPDoc($this->reflection->getProperty($propertyName));

            $values = array('default' => $doc['default']);
            if (strpos($doc['cast'], 'array') !== false) {
                $values['flags'] = FILTER_REQUIRE_ARRAY;
                $cast = str_replace('array', null, $doc['cast']);
            } else {
                $cast = $doc['cast'];
            }

            if (!array_key_exists($cast, $availablefilters)) {
                throw new Exception("cast '{$cast}' not available");
            }
            $values['filter'] = $availablefilters[$cast];
            $args[$propertyName] = $values;
        }

        $inputMethods = array('GET' => INPUT_GET, 'POST' => INPUT_POST);
        $inputMethod = $inputMethods[$_SERVER['REQUEST_METHOD']];

        $myinputs = filter_input_array($inputMethod, $args);

        foreach ($myinputs as $propertyName => $filteredInput) {
            if (is_null($filteredInput) && ! is_null($args[$propertyName])) {
                $this->$propertyName = filter_var($args[$propertyName]['default'], $args[$propertyName]['filter']);
            } else {
                $this->$propertyName = $filteredInput;
            }
            $this->filteredParameters[$propertyName] = $this->$propertyName;

        }
        
        if ($validateInConstructor === true) {
            $this->validateAll();
        }
    }

    private $filteredParameters = array();

    public function getFilteredParameters()
    {
        return $this->filteredParameters;
    }

    private function filterInput($cast, $key, $default)
    {
        $inputMethods = array('GET' => INPUT_GET, 'POST' => INPUT_POST);

        if (!array_key_exists($_SERVER['REQUEST_METHOD'], $inputMethods)) {
            throw new Exception("Input method not supported");
        }
        
        $inputMethod = $inputMethods[$_SERVER['REQUEST_METHOD']];

        $filters = $this->getAvailableFilters();
        $filter = $filters[$cast];
        $out = filter_input($inputMethod, $key, $filter);

        if (is_null($out) && $default != '') {
            $out = filter_var($default, $filter);
        }
        return $out;
    }

    private function getAvailableFilters()
    {
        $filters = array(
            'string'  => FILTER_SANITIZE_STRING,
            'int'     => FILTER_VALIDATE_INT,
            'integer' => FILTER_VALIDATE_INT,
            'bool'    => FILTER_VALIDATE_BOOLEAN,
            'float'   => FILTER_VALIDATE_FLOAT,
            'url'     => FILTER_VALIDATE_EMAIL,
            'email'   => FILTER_SANITIZE_STRING,
            'raw'     => FILTER_UNSAFE_RAW
        );
        return $filters;
    }

    public function validateAll()
    {
        $validationErrors = array();
        foreach (array_keys($this->parameters) as $parameter) {
            $validateMethod = "validate_{$parameter}";
            if ($this->reflection->hasMethod($validateMethod) || isset($this->dinamicCallbacks[$validateMethod])) {
                $parameter = $this->$parameter;
                if ($this->$validateMethod($parameter) === false) {
                    $validationErrors[$parameter] = array(
                        'value' => $this->$parameter
                    );
                }
            }
        }

        if (count($validationErrors) > 0) {
            throw new RequestObjectException('Validation error', $validationErrors);
        }
    }

    private $dinamicCallbacks = array();

    public function appendValidateTo($property, Closure $callback)
    {
        $this->dinamicCallbacks["validate_{$property}"] = $callback;
    }

    public function __call($name, $arguments)
    {
        if (isset($this->dinamicCallbacks[$name])) {
            return call_user_func_array($this->dinamicCallbacks[$name], $arguments);
        } else {
            return null;
        }
    }
}
