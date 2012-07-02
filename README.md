# Example 1: simple example</h2>
    class Request1 extends RequestObject
    {
        public $param1;
    }

    $request = new Request1();
    echo "param1: " . $request->param1 . '<p/>';

output:

    test1.php?param1=11212
    param1: 11212

    test1.php?param1=hi
    param1: hi


# Example 2: data types and default values

```php
    class Request2 extends RequestObject
    {
        /**
         * @cast string
         */
        public $param1;
        /**
         * @cast string
         * @default default value
         */
        public $param2;
    }

    $request = new Request2();

    echo "param1: <br/>";
    var_dump($request->param1);
    echo "<br/>";

    echo "param2: <br/>";
    var_dump($request->param2);
    echo "<br/>";
```

output:

    test2.php?param1=hi&amp;param2=1

    param1: string(2) "hi"
    param2: string(1) "1"

    test2.php?param1=1&amp;param2=hi

    param1: string(1) "1"
    param2: string(2) "hi"

    test2.php?param1=1
    param1: string(1) "1"
    param2: string(13) "default value"

# Example 3: validadors
```php
    class Request3 extends RequestObject
    {
        /** @cast string */
        public $param1;
        /** @cast integer */
        public $param2;

        protected function validate_param1(&$value)
        {
            $value = strrev($value);
        }
        
        protected function validate_param2($value)
        {
            if ($value == 1) {
                return false;
            }
        }
    }
    try {
        $request = new Request3();

        echo "param1: <br/>";
        var_dump($request->param1);
        echo "<br/>";

        echo "param2: <br/>";
        var_dump($request->param2);
        echo "<br/>";
    } catch (RequestObjectException $e) {
        echo $e->getMessage();
        echo "<br/>";
        var_dump($e->getValidationErrors());
    }
```

output:

    test3.php?param2=2&amp;param1=hi
    param1: string(2) "ih"
    param2: int(2)

    test3.php?param1=hola&amp;param2=1
    Validation error
    array(1) { ["param2"]=&gt; array(1) { ["value"]=&gt; int(1) } }

# Example 4: Dynamic validations

```php
    class Request4 extends RequestObject
    {
        /** @cast string */
        public $param1;
        /** @cast integer */
        public $param2;
    }

    $request = new Request4(false); // disables perform validation on contructor
                                   // it means it will not raise any validation exception
    $request->appendValidateTo('param2', function($value) {
            if ($value == 1) {
                return false;
            }
        });

    try {
        $request->validateAll(); // now we perform the validation

        echo "param1: <br/>";
        var_dump($request->param1);
        echo "<br/>";

        echo "param2: <br/>";
        var_dump($request->param2);
        echo "<br/>";
    } catch (RequestObjectException $e) {
        echo $e->getMessage();
        echo "<br/>";
        var_dump($e->getValidationErrors());
    }
```

output:

    test4.php?param1=hi&amp;param2=2
    param1: string(4) "hi"
    param2: int(2)

    test4.php?param1=hola&amp;param2=1
    Validation error
    array(1) { ["param2"]=&gt; array(1) { ["value"]=&gt; int(1) } }



# Example 5: Arrays and default params

```php
    class Request5 extends RequestObject
    {
        /** @cast arrayString */
        public $param1;

        /** @cast integer */
        public $param2;

        /**
         * @cast arrayString
         * @defaultArray "hello", "world"
         */
        public $param3;

        protected function validate_param2(&$value)
        {
            $value++;
        }
    }

    $request = new Request5();

    echo "<p>param1: </p>";
    var_dump($request->param1);

    echo "<p>param2: </p>";
    var_dump($request->param2);

    echo "<p>param3: </p>";
    var_dump($request->param3);
```

output:

    test5.php?param1[]=1&amp;param1[]=2&amp;param2[]=hi
    param1: array(2) { [0]=&gt; int(1) [1]=&gt; int(2) }
    param2: int(1)
    param3: array(2) { [0]=&gt; string(5) "hello" [1]=&gt; string(5) "world" }

    test5.php?param1[]=1&amp;param1[]=2&amp;param2=2
    param1: array(2) { [0]=&gt; string(1) "1" [1]=&gt; string(1) "2" }
    param2: int(3)
    param3: array(2) { [0]=&gt; string(5) "hello" [1]=&gt; string(5) "world" }
