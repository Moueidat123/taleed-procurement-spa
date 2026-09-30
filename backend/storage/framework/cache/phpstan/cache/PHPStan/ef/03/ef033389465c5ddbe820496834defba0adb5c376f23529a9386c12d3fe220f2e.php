<?php declare(strict_types = 1);

// osfsl-/Users/user/Documents/GitHub/taleed-procurement-spa/backend/vendor/composer/../laravel/fortify/src/Features.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Laravel\Fortify\Features
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-e91a8ed9f72202fc58fc3e4010918a8a10ea1d8d1980f74aa9fa0fdb611eb465-8.4.14-6.73.0.5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Laravel\\Fortify\\Features',
        'filename' => '/Users/user/Documents/GitHub/taleed-procurement-spa/backend/vendor/composer/../laravel/fortify/src/Features.php',
      ),
    ),
    'namespace' => 'Laravel\\Fortify',
    'name' => 'Laravel\\Fortify\\Features',
    'shortName' => 'Features',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 5,
    'endLine' => 175,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => NULL,
    'implementsClassNames' => 
    array (
    ),
    'traitClassNames' => 
    array (
    ),
    'immediateConstants' => 
    array (
    ),
    'immediateProperties' => 
    array (
    ),
    'immediateMethods' => 
    array (
      'enabled' => 
      array (
        'name' => 'enabled',
        'parameters' => 
        array (
          'feature' => 
          array (
            'name' => 'feature',
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
            'startLine' => 13,
            'endLine' => 13,
            'startColumn' => 36,
            'endColumn' => 50,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the given feature is enabled.
 *
 * @param  string  $feature
 * @return bool
 */',
        'startLine' => 13,
        'endLine' => 16,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'optionEnabled' => 
      array (
        'name' => 'optionEnabled',
        'parameters' => 
        array (
          'feature' => 
          array (
            'name' => 'feature',
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
            'startLine' => 25,
            'endLine' => 25,
            'startColumn' => 42,
            'endColumn' => 56,
            'parameterIndex' => 0,
            'isOptional' => false,
          ),
          'option' => 
          array (
            'name' => 'option',
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
            'startLine' => 25,
            'endLine' => 25,
            'startColumn' => 59,
            'endColumn' => 72,
            'parameterIndex' => 1,
            'isOptional' => false,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the feature is enabled and has a given option enabled.
 *
 * @param  string  $feature
 * @param  string  $option
 * @return bool
 */',
        'startLine' => 25,
        'endLine' => 29,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'hasProfileFeatures' => 
      array (
        'name' => 'hasProfileFeatures',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the application is using any features that require "profile" management.
 *
 * @return bool
 */',
        'startLine' => 36,
        'endLine' => 42,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'canUpdateProfileInformation' => 
      array (
        'name' => 'canUpdateProfileInformation',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the application can update a user\'s profile information.
 *
 * @return bool
 */',
        'startLine' => 49,
        'endLine' => 52,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'hasSecurityFeatures' => 
      array (
        'name' => 'hasSecurityFeatures',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the application is using any security profile features.
 *
 * @return bool
 */',
        'startLine' => 59,
        'endLine' => 64,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'canUpdatePasswords' => 
      array (
        'name' => 'canUpdatePasswords',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the application can update user passwords.
 *
 * @return bool
 */',
        'startLine' => 71,
        'endLine' => 74,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'canManageTwoFactorAuthentication' => 
      array (
        'name' => 'canManageTwoFactorAuthentication',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the application can manage two factor authentication.
 *
 * @return bool
 */',
        'startLine' => 81,
        'endLine' => 84,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'canManagePasskeys' => 
      array (
        'name' => 'canManagePasskeys',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Determine if the application can manage passkeys.
 *
 * @return bool
 */',
        'startLine' => 91,
        'endLine' => 94,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'registration' => 
      array (
        'name' => 'registration',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Enable the registration feature.
 *
 * @return string
 */',
        'startLine' => 101,
        'endLine' => 104,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'resetPasswords' => 
      array (
        'name' => 'resetPasswords',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Enable the password reset feature.
 *
 * @return string
 */',
        'startLine' => 111,
        'endLine' => 114,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'emailVerification' => 
      array (
        'name' => 'emailVerification',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Enable the email verification feature.
 *
 * @return string
 */',
        'startLine' => 121,
        'endLine' => 124,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'updateProfileInformation' => 
      array (
        'name' => 'updateProfileInformation',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Enable the update profile information feature.
 *
 * @return string
 */',
        'startLine' => 131,
        'endLine' => 134,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'updatePasswords' => 
      array (
        'name' => 'updatePasswords',
        'parameters' => 
        array (
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Enable the update password feature.
 *
 * @return string
 */',
        'startLine' => 141,
        'endLine' => 144,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'twoFactorAuthentication' => 
      array (
        'name' => 'twoFactorAuthentication',
        'parameters' => 
        array (
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 152,
                'endLine' => 152,
                'startTokenPos' => 460,
                'startFilePos' => 3570,
                'endTokenPos' => 461,
                'endFilePos' => 3571,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 152,
            'endLine' => 152,
            'startColumn' => 52,
            'endColumn' => 70,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Enable the two factor authentication feature.
 *
 * @param  array  $options
 * @return string
 */',
        'startLine' => 152,
        'endLine' => 159,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
        'aliasName' => NULL,
      ),
      'passkeys' => 
      array (
        'name' => 'passkeys',
        'parameters' => 
        array (
          'options' => 
          array (
            'name' => 'options',
            'default' => 
            array (
              'code' => '[]',
              'attributes' => 
              array (
                'startLine' => 167,
                'endLine' => 167,
                'startTokenPos' => 516,
                'startFilePos' => 3919,
                'endTokenPos' => 517,
                'endFilePos' => 3920,
              ),
            ),
            'type' => 
            array (
              'class' => 'PHPStan\\BetterReflection\\Reflection\\ReflectionNamedType',
              'data' => 
              array (
                'name' => 'array',
                'isIdentifier' => true,
              ),
            ),
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 167,
            'endLine' => 167,
            'startColumn' => 37,
            'endColumn' => 55,
            'parameterIndex' => 0,
            'isOptional' => true,
          ),
        ),
        'returnsReference' => false,
        'returnType' => NULL,
        'attributes' => 
        array (
        ),
        'docComment' => '/**
 * Enable the passkeys feature.
 *
 * @param  array  $options
 * @return string
 */',
        'startLine' => 167,
        'endLine' => 174,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Laravel\\Fortify',
        'declaringClassName' => 'Laravel\\Fortify\\Features',
        'implementingClassName' => 'Laravel\\Fortify\\Features',
        'currentClassName' => 'Laravel\\Fortify\\Features',
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