<?php declare(strict_types = 1);

// osfsl-/Users/user/Documents/GitHub/taleed-procurement-spa/backend/vendor/composer/../statamic/cms/src/StaticCaching/Replacers/NoCacheReplacer.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Statamic\StaticCaching\Replacers\NoCacheReplacer
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-dedbfe56825551ba78b191b360c4759e6e6c6313bcffcaf2ffb0da4d9aa842be-8.4.14-6.73.0.5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'filename' => '/Users/user/Documents/GitHub/taleed-procurement-spa/backend/vendor/composer/../statamic/cms/src/StaticCaching/Replacers/NoCacheReplacer.php',
      ),
    ),
    'namespace' => 'Statamic\\StaticCaching\\Replacers',
    'name' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
    'shortName' => 'NoCacheReplacer',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 12,
    'endLine' => 104,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
      0 => 'Statamic\\StaticCaching\\Replacer',
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
      'PATTERN' => 
      array (
        'declaringClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'implementingClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'name' => 'PATTERN',
        'modifiers' => 1,
        'type' => NULL,
        'value' => 
        array (
          'code' => '\'/<span class="nocache" data-nocache="([\\w\\d]+)">NOCACHE_PLACEHOLDER<\\/span>/\'',
          'attributes' => 
          array (
            'startLine' => 14,
            'endLine' => 14,
            'startTokenPos' => 53,
            'startFilePos' => 344,
            'endTokenPos' => 53,
            'endFilePos' => 421,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 14,
        'endLine' => 14,
        'startColumn' => 5,
        'endColumn' => 99,
      ),
    ),
    'immediateProperties' => 
    array (
      'session' => 
      array (
        'declaringClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'implementingClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'name' => 'session',
        'modifiers' => 4,
        'type' => NULL,
        'default' => NULL,
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 16,
        'endLine' => 16,
        'startColumn' => 5,
        'endColumn' => 21,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
    ),
    'immediateMethods' => 
    array (
      '__construct' => 
      array (
        'name' => '__construct',
        'parameters' => 
        array (
          'session' => 
          array (
            'name' => 'session',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Statamic\\StaticCaching\\NoCache\\Session',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 18,
            'endLine' => 18,
            'startColumn' => 33,
            'endColumn' => 48,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 18,
        'endLine' => 21,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\StaticCaching\\Replacers',
        'declaringClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'implementingClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'currentClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'aliasName' => NULL,
      ),
      'prepareResponseToCache' => 
      array (
        'name' => 'prepareResponseToCache',
        'parameters' => 
        array (
          'responseToBeCached' => 
          array (
            'name' => 'responseToBeCached',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Http\\Response',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 23,
            'endLine' => 23,
            'startColumn' => 44,
            'endColumn' => 71,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'initialResponse' => 
          array (
            'name' => 'initialResponse',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Http\\Response',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 23,
            'endLine' => 23,
            'startColumn' => 74,
            'endColumn' => 98,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 23,
        'endLine' => 33,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\StaticCaching\\Replacers',
        'declaringClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'implementingClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'currentClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'aliasName' => NULL,
      ),
      'replaceInCachedResponse' => 
      array (
        'name' => 'replaceInCachedResponse',
        'parameters' => 
        array (
          'response' => 
          array (
            'name' => 'response',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Http\\Response',
                'isIdentifier' => false,
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
            'startColumn' => 45,
            'endColumn' => 62,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 35,
        'endLine' => 38,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\StaticCaching\\Replacers',
        'declaringClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'implementingClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'currentClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'aliasName' => NULL,
      ),
      'replaceInResponse' => 
      array (
        'name' => 'replaceInResponse',
        'parameters' => 
        array (
          'response' => 
          array (
            'name' => 'response',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Http\\Response',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 40,
            'endLine' => 40,
            'startColumn' => 40,
            'endColumn' => 57,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 40,
        'endLine' => 49,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Statamic\\StaticCaching\\Replacers',
        'declaringClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'implementingClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'currentClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'aliasName' => NULL,
      ),
      'includeJs' => 
      array (
        'name' => 'includeJs',
        'parameters' => 
        array (
          'response' => 
          array (
            'name' => 'response',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Http\\Response',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 51,
            'endLine' => 51,
            'startColumn' => 32,
            'endColumn' => 49,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 51,
        'endLine' => 60,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Statamic\\StaticCaching\\Replacers',
        'declaringClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'implementingClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'currentClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'aliasName' => NULL,
      ),
      'replace' => 
      array (
        'name' => 'replace',
        'parameters' => 
        array (
          'content' => 
          array (
            'name' => 'content',
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
            'startLine' => 62,
            'endLine' => 62,
            'startColumn' => 29,
            'endColumn' => 43,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 62,
        'endLine' => 73,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Statamic\\StaticCaching\\Replacers',
        'declaringClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'implementingClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'currentClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'aliasName' => NULL,
      ),
      'performReplacement' => 
      array (
        'name' => 'performReplacement',
        'parameters' => 
        array (
          'content' => 
          array (
            'name' => 'content',
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
            'startLine' => 75,
            'endLine' => 75,
            'startColumn' => 41,
            'endColumn' => 55,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 75,
        'endLine' => 84,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Statamic\\StaticCaching\\Replacers',
        'declaringClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'implementingClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'currentClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'aliasName' => NULL,
      ),
      'modifyFullMeasureResponse' => 
      array (
        'name' => 'modifyFullMeasureResponse',
        'parameters' => 
        array (
          'response' => 
          array (
            'name' => 'response',
            'default' => NULL,
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'Illuminate\\Http\\Response',
                'isIdentifier' => false,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 86,
            'endLine' => 86,
            'startColumn' => 48,
            'endColumn' => 65,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 86,
        'endLine' => 103,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 4,
        'namespace' => 'Statamic\\StaticCaching\\Replacers',
        'declaringClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'implementingClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'currentClassName' => 'Statamic\\StaticCaching\\Replacers\\NoCacheReplacer',
        'aliasName' => NULL,
      ),
    ),
    'traitsData' => 
    array (
      'aliases' => 
      array (
      ),
      'modifiers' => 
      array (
      ),
      'precedences' => 
      array (
      ),
      'hashes' => 
      array (
      ),
    ),
  ),
));