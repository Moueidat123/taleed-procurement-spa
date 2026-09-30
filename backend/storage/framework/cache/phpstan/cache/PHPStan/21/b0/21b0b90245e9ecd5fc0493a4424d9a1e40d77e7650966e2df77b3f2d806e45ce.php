<?php declare(strict_types = 1);

// phpinternal-PHPStan\BetterReflection\Reflection\ReflectionFunction-file_get_contents
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v3-6.73.0.5-dev-master@e4f5f6c-8.4.14-',
   'data' => 
  array (
    'name' => 'file_get_contents',
    'parameters' => 
    array (
      'filename' => 
      array (
        'name' => 'filename',
        'default' => NULL,
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
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\FileReference',
            'isRepeated' => false,
            'arguments' => 
            array (
            ),
          ),
        ),
        'startLine' => 30,
        'endLine' => 31,
        'startColumn' => 9,
        'endColumn' => 24,
        'parameterIndex' => 0,
        'isOptional' => false,
      ),
      'use_include_path' => 
      array (
        'name' => 'use_include_path',
        'default' => 
        array (
          'code' => '\\false',
          'attributes' => 
          array (
            'startLine' => 32,
            'endLine' => 32,
            'startTokenPos' => 35,
            'startFilePos' => 1129,
            'endTokenPos' => 35,
            'endFilePos' => 1133,
          ),
        ),
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'bool',
            'isIdentifier' => true,
          ),
        ),
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 32,
        'endLine' => 32,
        'startColumn' => 9,
        'endColumn' => 38,
        'parameterIndex' => 1,
        'isOptional' => true,
      ),
      'context' => 
      array (
        'name' => 'context',
        'default' => 
        array (
          'code' => '\\null',
          'attributes' => 
          array (
            'startLine' => 33,
            'endLine' => 33,
            'startTokenPos' => 42,
            'startFilePos' => 1155,
            'endTokenPos' => 42,
            'endFilePos' => 1158,
          ),
        ),
        'type' => NULL,
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 33,
        'endLine' => 33,
        'startColumn' => 9,
        'endColumn' => 23,
        'parameterIndex' => 2,
        'isOptional' => true,
      ),
      'offset' => 
      array (
        'name' => 'offset',
        'default' => 
        array (
          'code' => '0',
          'attributes' => 
          array (
            'startLine' => 34,
            'endLine' => 34,
            'startTokenPos' => 51,
            'startFilePos' => 1183,
            'endTokenPos' => 51,
            'endFilePos' => 1183,
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
        'startLine' => 34,
        'endLine' => 34,
        'startColumn' => 9,
        'endColumn' => 23,
        'parameterIndex' => 3,
        'isOptional' => true,
      ),
      'length' => 
      array (
        'name' => 'length',
        'default' => 
        array (
          'code' => '\\null',
          'attributes' => 
          array (
            'startLine' => 35,
            'endLine' => 35,
            'startTokenPos' => 61,
            'startFilePos' => 1209,
            'endTokenPos' => 61,
            'endFilePos' => 1212,
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
                  'name' => 'int',
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
        'startLine' => 35,
        'endLine' => 35,
        'startColumn' => 9,
        'endColumn' => 27,
        'parameterIndex' => 4,
        'isOptional' => true,
      ),
    ),
    'returnsReference' => false,
    'returnType' => 
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
              'name' => 'false',
              'isIdentifier' => true,
            ),
          ),
        ),
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
          0 => 
          array (
            'code' => '\\true',
            'attributes' => 
            array (
              'startLine' => 28,
              'endLine' => 28,
              'startTokenPos' => 11,
              'startFilePos' => 986,
              'endTokenPos' => 11,
              'endFilePos' => 989,
            ),
          ),
        ),
      ),
    ),
    'docComment' => '/**
 * Reads entire file into a string
 * @link https://php.net/manual/en/function.file-get-contents.php
 * @param string $filename <p>
 * Name of the file to read.
 * </p>
 * @param bool $use_include_path [optional] <p>
 * Note: As of PHP 5 the FILE_USE_INCLUDE_PATH constant can be
 * used to trigger include path search.
 * </p>
 * @param resource $context [optional] <p>
 * A valid context resource created with
 * stream_context_create. If you don\'t need to use a
 * custom context, you can skip this parameter by null.
 * </p>
 * @param int $offset [optional] <p>
 * The offset where the reading starts.
 * </p>
 * @param int|null $length [optional] <p>
 * Maximum length of data read. The default is to read until end
 * of file is reached.
 * </p>
 * @return string|false The function returns the read data or false on failure.
 */',
    'startLine' => 28,
    'endLine' => 38,
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
        'name' => 'file_get_contents',
        'filename' => 'phpstorm-stubs:standard/standard_5.stub',
        'extensionName' => 'standard',
        'aliasName' => NULL,
      ),
    ),
  ),
));