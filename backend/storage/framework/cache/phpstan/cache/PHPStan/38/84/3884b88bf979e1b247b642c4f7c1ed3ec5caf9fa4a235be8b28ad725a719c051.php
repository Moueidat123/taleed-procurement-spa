<?php declare(strict_types = 1);

// odsl-/Users/user/Documents/GitHub/taleed-procurement-spa/backend/app/Http/Responses/GenericPasswordResetLinkResponse.php-PHPStan\BetterReflection\Reflection\ReflectionClass-App\Http\Responses\GenericPasswordResetLinkResponse
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-6.73.0.5-8.4.14-d7d9269a78b8fae4dce131283d8bcfe9ea580a7ec8a0a7124a5730f9233ad1e0',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'App\\Http\\Responses\\GenericPasswordResetLinkResponse',
        'filename' => '/Users/user/Documents/GitHub/taleed-procurement-spa/backend/app/Http/Responses/GenericPasswordResetLinkResponse.php',
      ),
    ),
    'namespace' => 'App\\Http\\Responses',
    'name' => 'App\\Http\\Responses\\GenericPasswordResetLinkResponse',
    'shortName' => 'GenericPasswordResetLinkResponse',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => '/**
 * Identical acknowledgement whether or not the email belongs to an active
 * application user, and whether or not the request was throttled, so the
 * endpoint cannot be used to discover accounts (05-ACCEPTANCE.md).
 */',
    'attributes' => 
    array (
    ),
    'startLine' => 14,
    'endLine' => 24,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
      0 => 'Laravel\\Fortify\\Contracts\\FailedPasswordResetLinkRequestResponse',
      1 => 'Laravel\\Fortify\\Contracts\\SuccessfulPasswordResetLinkRequestResponse',
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
      'status' => 
      array (
        'declaringClassName' => 'App\\Http\\Responses\\GenericPasswordResetLinkResponse',
        'implementingClassName' => 'App\\Http\\Responses\\GenericPasswordResetLinkResponse',
        'name' => 'status',
        'modifiers' => 2,
        'type' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'string',
            'isIdentifier' => true,
          ),
        ),
        'default' => 
        array (
          'code' => '\'\'',
          'attributes' => 
          array (
            'startLine' => 16,
            'endLine' => 16,
            'startTokenPos' => 51,
            'startFilePos' => 636,
            'endTokenPos' => 51,
            'endFilePos' => 637,
          ),
        ),
        'docComment' => NULL,
        'attributes' => 
        array (
        ),
        'startLine' => 16,
        'endLine' => 16,
        'startColumn' => 33,
        'endColumn' => 61,
        'isPromoted' => true,
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
          'status' => 
          array (
            'name' => 'status',
            'default' => 
            array (
              'code' => '\'\'',
              'attributes' => 
              array (
                'startLine' => 16,
                'endLine' => 16,
                'startTokenPos' => 51,
                'startFilePos' => 636,
                'endTokenPos' => 51,
                'endFilePos' => 637,
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
            'isPromoted' => true,
            'attributes' => 
            array (
            ),
            'startLine' => 16,
            'endLine' => 16,
            'startColumn' => 33,
            'endColumn' => 61,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 16,
        'endLine' => 16,
        'startColumn' => 5,
        'endColumn' => 65,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Http\\Responses',
        'declaringClassName' => 'App\\Http\\Responses\\GenericPasswordResetLinkResponse',
        'implementingClassName' => 'App\\Http\\Responses\\GenericPasswordResetLinkResponse',
        'currentClassName' => 'App\\Http\\Responses\\GenericPasswordResetLinkResponse',
        'aliasName' => NULL,
      ),
      'toResponse' => 
      array (
        'name' => 'toResponse',
        'parameters' => 
        array (
          'request' => 
          array (
            'name' => 'request',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 18,
            'endLine' => 18,
            'startColumn' => 32,
            'endColumn' => 39,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => 
        array (
          'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
          'data' => 
          array (
            'name' => 'Illuminate\\Http\\JsonResponse',
            'isIdentifier' => false,
          ),
        ),
        'attributes' => 
        array (
        ),
        'docComment' => NULL,
        'startLine' => 18,
        'endLine' => 23,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'App\\Http\\Responses',
        'declaringClassName' => 'App\\Http\\Responses\\GenericPasswordResetLinkResponse',
        'implementingClassName' => 'App\\Http\\Responses\\GenericPasswordResetLinkResponse',
        'currentClassName' => 'App\\Http\\Responses\\GenericPasswordResetLinkResponse',
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