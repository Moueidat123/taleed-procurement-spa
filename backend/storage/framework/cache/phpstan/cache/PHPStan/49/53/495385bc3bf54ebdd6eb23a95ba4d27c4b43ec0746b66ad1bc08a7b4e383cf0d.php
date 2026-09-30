<?php declare(strict_types = 1);

// phpinternal-PHPStan\BetterReflection\Reflection\ReflectionFunction-http_build_query
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v3-6.73.0.5-dev-master@e4f5f6c-8.4.14-',
   'data' => 
  array (
    'name' => 'http_build_query',
    'parameters' => 
    array (
      'data' => 
      array (
        'name' => 'data',
        'default' => NULL,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'object',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'array',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 38,
        'endLine' => 38,
        'startColumn' => 31,
        'endColumn' => 48,
        'parameterIndex' => 0,
        'isOptional' => false,
      ),
      'numeric_prefix' => 
      array (
        'name' => 'numeric_prefix',
        'default' => 
        array (
          'code' => '""',
          'attributes' => 
          array (
            'startLine' => 38,
            'endLine' => 38,
            'startTokenPos' => 29,
            'startFilePos' => 1690,
            'endTokenPos' => 29,
            'endFilePos' => 1691,
          ),
        ),
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 38,
        'endLine' => 38,
        'startColumn' => 51,
        'endColumn' => 77,
        'parameterIndex' => 1,
        'isOptional' => true,
      ),
      'arg_separator' => 
      array (
        'name' => 'arg_separator',
        'default' => 
        array (
          'code' => '\\null',
          'attributes' => 
          array (
            'startLine' => 38,
            'endLine' => 38,
            'startTokenPos' => 39,
            'startFilePos' => 1719,
            'endTokenPos' => 39,
            'endFilePos' => 1722,
          ),
        ),
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionUnionType',
          'data' => 
          array (
            'types' => 
            array (
              0 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'string',
                  'isIdentifier' => true,
                ),
              ),
              1 => 
              array (
                'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
                'data' => 
                array (
                  'name' => 'null',
                  'isIdentifier' => true,
                ),
              ),
            ),
          ),
        ),
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 38,
        'endLine' => 38,
        'startColumn' => 80,
        'endColumn' => 108,
        'parameterIndex' => 2,
        'isOptional' => true,
      ),
      'encoding_type' => 
      array (
        'name' => 'encoding_type',
        'default' => 
        array (
          'code' => '\\PHP_QUERY_RFC1738',
          'attributes' => 
          array (
            'startLine' => 38,
            'endLine' => 38,
            'startTokenPos' => 48,
            'startFilePos' => 1746,
            'endTokenPos' => 48,
            'endFilePos' => 1762,
          ),
        ),
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'int',
            'isIdentifier' => true,
          ),
        ),
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 38,
        'endLine' => 38,
        'startColumn' => 111,
        'endColumn' => 148,
        'parameterIndex' => 3,
        'isOptional' => true,
      ),
    ),
    'returnsReference' => false,
    'returnType' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
      'data' => 
      array (
        'name' => 'string',
        'isIdentifier' => true,
      ),
    ),
    'attributes' => 
    array (
      0 => 
      array (
        'name' => 'JetBrains\\PhpStorm\\Pure',
        'isRepeated' => false,
        'arguments' => 
        array (
        ),
      ),
    ),
    'docComment' => '/**
 * Generate URL-encoded query string
 * @link https://php.net/manual/en/function.http-build-query.php
 * @param object|array $data <p>
 * May be an array or object containing properties.
 * </p>
 * <p>
 * If query_data is an array, it may be a simple one-dimensional structure,
 * or an array of arrays (which in turn may contain other arrays).
 * </p>
 * <p>
 * If query_data is an object, then only public properties will be incorporated into the result.
 * </p>
 * @param string $numeric_prefix [optional] <p>
 * If numeric indices are used in the base array and this parameter is
 * provided, it will be prepended to the numeric index for elements in
 * the base array only.
 * </p>
 * <p>
 * This is meant to allow for legal variable names when the data is
 * decoded by PHP or another CGI application later on.
 * </p>
 * @param string|null $arg_separator <p>
 * arg_separator.output
 * is used to separate arguments, unless this parameter is specified,
 * and is then used.
 * </p>
 * @param int $encoding_type By default, PHP_QUERY_RFC1738.
 *  <p>If enc_type is PHP_QUERY_RFC1738, then encoding is performed per » RFC 1738 and the application/x-www-form-urlencoded media type,
 *  which implies that spaces are encoded as plus (+) signs.
 *  <p>If enc_type is PHP_QUERY_RFC3986, then encoding is performed according to » RFC 3986, and spaces will be percent encoded (%20).
 * @return string a URL-encoded string.
 */',
    'startLine' => 37,
    'endLine' => 40,
    'startColumn' => 5,
    'endColumn' => 5,
    'couldThrow' => false,
    'isClosure' => false,
    'isGenerator' => false,
    'isVariadic' => false,
    'isStatic' => false,
    'namespace' => NULL,
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\InternalLocatedSource',
      'data' => 
      array (
        'name' => 'http_build_query',
        'filename' => 'phpstorm-stubs:standard/standard_2.stub',
        'extensionName' => 'standard',
        'aliasName' => NULL,
      ),
    ),
  ),
));