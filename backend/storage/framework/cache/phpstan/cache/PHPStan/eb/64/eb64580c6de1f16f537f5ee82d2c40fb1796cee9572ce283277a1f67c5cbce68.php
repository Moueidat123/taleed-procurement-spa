<?php declare(strict_types = 1);

// phpinternal-PHPStan\BetterReflection\Reflection\ReflectionFunction-file
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v3-6.73.0.5-dev-master@e4f5f6c-8.4.14-',
   'data' => 
  array (
    'name' => 'file',
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
        'startLine' => 37,
        'endLine' => 38,
        'startColumn' => 9,
        'endColumn' => 24,
        'parameterIndex' => 0,
        'isOptional' => false,
      ),
      'flags' => 
      array (
        'name' => 'flags',
        'default' => 
        array (
          'code' => '0',
          'attributes' => 
          array (
            'startLine' => 39,
            'endLine' => 39,
            'startTokenPos' => 35,
            'startFilePos' => 1509,
            'endTokenPos' => 35,
            'endFilePos' => 1509,
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
        'startLine' => 39,
        'endLine' => 39,
        'startColumn' => 9,
        'endColumn' => 22,
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
            'startLine' => 40,
            'endLine' => 40,
            'startTokenPos' => 42,
            'startFilePos' => 1531,
            'endTokenPos' => 42,
            'endFilePos' => 1534,
          ),
        ),
        'type' => NULL,
        'isVariadic' => false,
        'byRef' => false,
        'isPromoted' => false,
        'attributes' => 
        array (
        ),
        'startLine' => 40,
        'endLine' => 40,
        'startColumn' => 9,
        'endColumn' => 23,
        'parameterIndex' => 2,
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
              'name' => 'array',
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
              'startLine' => 35,
              'endLine' => 35,
              'startTokenPos' => 11,
              'startFilePos' => 1391,
              'endTokenPos' => 11,
              'endFilePos' => 1394,
            ),
          ),
        ),
      ),
    ),
    'docComment' => '/**
 * Reads entire file into an array
 * @link https://php.net/manual/en/function.file.php
 * @param string $filename <p>
 * Path to the file.
 * </p>
 * @param int $flags <p>
 * The optional parameter flags can be one, or
 * more, of the following constants:
 * <ul>
 * <li><tt>FILE_USE_INCLUDE_PATH</tt> - Search for the file in the include_path.</li>
 * <li><tt>FILE_IGNORE_NEW_LINES</tt> - Omit newline at the end of each array element</li>
 * <li><tt>FILE_SKIP_EMPTY_LINES</tt> - Skip empty lines</li>
 * </ul>
 * </p>
 * @param resource $context [optional] <p>
 * A context resource created with the
 * stream_context_create function.
 * </p>
 * @return array|false the file in an array. Each element of the array corresponds to a
 * line in the file, with the newline still attached. Upon failure,
 * file returns false.
 * <p>
 * Each line in the resulting array will include the line ending, unless
 * FILE_IGNORE_NEW_LINES is used, so you still need to
 * use rtrim if you do not want the line ending
 * present.
 * </p>
 * @throws \\ValueError As of PHP 8.3.0, throws a ValueError if flags includes any invalid values,
 * such as FILE_APPEND. Emits an E_WARNING level error if the file does not exist.
 */',
    'startLine' => 35,
    'endLine' => 43,
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
        'name' => 'file',
        'filename' => 'phpstorm-stubs:standard/standard_5.stub',
        'extensionName' => 'standard',
        'aliasName' => NULL,
      ),
    ),
  ),
));