<?php declare(strict_types = 1);

// phpinternal-PHPStan\BetterReflection\Reflection\ReflectionFunction-rtrim
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v3-6.73.0.5-dev-master@e4f5f6c-8.4.14-',
   'data' => 
  array (
    'name' => 'rtrim',
    'parameters' => 
    array (
      'string' => 
      array (
        'name' => 'string',
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
        ),
        'startLine' => 29,
        'endLine' => 29,
        'startColumn' => 9,
        'endColumn' => 22,
        'parameterIndex' => 0,
        'isOptional' => false,
      ),
      'characters' => 
      array (
        'name' => 'characters',
        'default' => 
        array (
          'code' => '" \\n\\r\\t\\v\\x00"',
          'attributes' => 
          array (
            'startLine' => 31,
            'endLine' => 31,
            'startTokenPos' => 38,
            'startFilePos' => 1154,
            'endTokenPos' => 38,
            'endFilePos' => 1168,
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
          0 => 
          array (
            'name' => 'JetBrains\\PhpStorm\\Internal\\PhpStormStubsElementAvailable',
            'isRepeated' => false,
            'arguments' => 
            array (
              'to' => 
              array (
                'code' => '\'8.5\'',
                'attributes' => 
                array (
                  'startLine' => 30,
                  'endLine' => 30,
                  'startTokenPos' => 28,
                  'startFilePos' => 1117,
                  'endTokenPos' => 28,
                  'endFilePos' => 1121,
                ),
              ),
            ),
          ),
        ),
        'startLine' => 30,
        'endLine' => 31,
        'startColumn' => 9,
        'endColumn' => 44,
        'parameterIndex' => 1,
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
 * Strip whitespace (or other characters) from the end of a string.
 * Without the second parameter, rtrim() will strip these characters:
 * <ul>
 * <li>" " (ASCII 32 (0x20)), an ordinary space.
 * <li>"\\t" (ASCII 9 (0x09)), a tab.
 * <li>"\\n" (ASCII 10 (0x0A)), a new line (line feed).
 * <li>"\\r" (ASCII 13 (0x0D)), a carriage return.
 * <li>"\\0" (ASCII 0 (0x00)), the NUL-byte.
 * <li>"\\x0B" (ASCII 11 (0x0B)), a vertical tab.
 * </ul>
 * @link https://php.net/manual/en/function.rtrim.php
 * @param string $string <p>
 * The input string.
 * </p>
 * @param string $characters [optional] <p>
 * You can also specify the characters you want to strip, by means
 * of the charlist parameter.
 * Simply list all characters that you want to be stripped. With
 * .. you can specify a range of characters.
 * </p>
 * @return string the modified string.
 */',
    'startLine' => 27,
    'endLine' => 34,
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
        'name' => 'rtrim',
        'filename' => 'phpstorm-stubs:standard/standard_1.stub',
        'extensionName' => 'standard',
        'aliasName' => NULL,
      ),
    ),
  ),
));