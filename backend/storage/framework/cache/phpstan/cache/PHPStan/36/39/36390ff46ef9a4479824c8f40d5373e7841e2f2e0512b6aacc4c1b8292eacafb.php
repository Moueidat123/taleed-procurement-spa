<?php declare(strict_types = 1);

// osfsl-/Users/user/Documents/GitHub/taleed-procurement-spa/backend/vendor/composer/../laravel/framework/src/Illuminate/Auth/Notifications/ResetPassword.php-PHPStan\BetterReflection\Reflection\ReflectionClass-Illuminate\Auth\Notifications\ResetPassword
return \PHPStan\Cache\CacheItem::__set_state(array(
   'variableKey' => 'v2-e9976963ae185318d03fc5d6a1afe8378593a7c2df3679ce1cb6f25a9e63a005-8.4.14-6.73.0.5',
   'data' => 
  array (
    'locatedSource' => 
    array (
      'class' => 'PHPStan\\BetterReflection\\SourceLocator\\Located\\LocatedSource',
      'data' => 
      array (
        'name' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'filename' => '/Users/user/Documents/GitHub/taleed-procurement-spa/backend/vendor/composer/../laravel/framework/src/Illuminate/Auth/Notifications/ResetPassword.php',
      ),
    ),
    'namespace' => 'Illuminate\\Auth\\Notifications',
    'name' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
    'shortName' => 'ResetPassword',
    'isInterface' => false,
    'isTrait' => false,
    'isEnum' => false,
    'isBackedEnum' => false,
    'modifiers' => 0,
    'docComment' => NULL,
    'attributes' => 
    array (
    ),
    'startLine' => 9,
    'endLine' => 123,
    'startColumn' => 1,
    'endColumn' => 1,
    'parentClassName' => 'Illuminate\\Notifications\\Notification',
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
      'token' => 
      array (
        'declaringClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'implementingClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'name' => 'token',
        'modifiers' => 1,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The password reset token.
 *
 * @var string
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 16,
        'endLine' => 16,
        'startColumn' => 5,
        'endColumn' => 18,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'createUrlCallback' => 
      array (
        'declaringClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'implementingClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'name' => 'createUrlCallback',
        'modifiers' => 17,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The callback that should be used to create the reset password URL.
 *
 * @var (\\Closure(mixed, string): string)|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 23,
        'endLine' => 23,
        'startColumn' => 5,
        'endColumn' => 37,
        'isPromoted' => false,
        'declaredAtCompileTime' => true,
        'immediateVirtual' => false,
        'immediateHooks' => 
        array (
        ),
      ),
      'toMailCallback' => 
      array (
        'declaringClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'implementingClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'name' => 'toMailCallback',
        'modifiers' => 17,
        'type' => NULL,
        'default' => NULL,
        'docComment' => '/**
 * The callback that should be used to build the mail message.
 *
 * @var (\\Closure(mixed, string): \\Illuminate\\Notifications\\Messages\\MailMessage|\\Illuminate\\Contracts\\Mail\\Mailable)|null
 */',
        'attributes' => 
        array (
        ),
        'startLine' => 30,
        'endLine' => 30,
        'startColumn' => 5,
        'endColumn' => 34,
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
          'token' => 
          array (
            'name' => 'token',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
              0 => 
              array (
                'name' => 'SensitiveParameter',
                'isRepeated' => false,
                'arguments' => 
                array (
                ),
              ),
            ),
            'startLine' => 37,
            'endLine' => 37,
            'startColumn' => 33,
            'endColumn' => 61,
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
 * Create a notification instance.
 *
 * @param  string  $token
 */',
        'startLine' => 37,
        'endLine' => 40,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Auth\\Notifications',
        'declaringClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'implementingClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'currentClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'aliasName' => NULL,
      ),
      'via' => 
      array (
        'name' => 'via',
        'parameters' => 
        array (
          'notifiable' => 
          array (
            'name' => 'notifiable',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 48,
            'endLine' => 48,
            'startColumn' => 25,
            'endColumn' => 35,
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
 * Get the notification\'s channels.
 *
 * @param  mixed  $notifiable
 * @return array|string
 */',
        'startLine' => 48,
        'endLine' => 51,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Auth\\Notifications',
        'declaringClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'implementingClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'currentClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'aliasName' => NULL,
      ),
      'toMail' => 
      array (
        'name' => 'toMail',
        'parameters' => 
        array (
          'notifiable' => 
          array (
            'name' => 'notifiable',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 59,
            'endLine' => 59,
            'startColumn' => 28,
            'endColumn' => 38,
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
 * Build the mail representation of the notification.
 *
 * @param  mixed  $notifiable
 * @return \\Illuminate\\Notifications\\Messages\\MailMessage
 */',
        'startLine' => 59,
        'endLine' => 66,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 1,
        'namespace' => 'Illuminate\\Auth\\Notifications',
        'declaringClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'implementingClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'currentClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'aliasName' => NULL,
      ),
      'buildMailMessage' => 
      array (
        'name' => 'buildMailMessage',
        'parameters' => 
        array (
          'url' => 
          array (
            'name' => 'url',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 74,
            'endLine' => 74,
            'startColumn' => 41,
            'endColumn' => 44,
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
 * Get the reset password notification mail message for the given URL.
 *
 * @param  string  $url
 * @return \\Illuminate\\Notifications\\Messages\\MailMessage
 */',
        'startLine' => 74,
        'endLine' => 82,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Auth\\Notifications',
        'declaringClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'implementingClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'currentClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'aliasName' => NULL,
      ),
      'resetUrl' => 
      array (
        'name' => 'resetUrl',
        'parameters' => 
        array (
          'notifiable' => 
          array (
            'name' => 'notifiable',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 90,
            'endLine' => 90,
            'startColumn' => 33,
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
        'docComment' => '/**
 * Get the reset URL for the given notifiable.
 *
 * @param  mixed  $notifiable
 * @return string
 */',
        'startLine' => 90,
        'endLine' => 100,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 2,
        'namespace' => 'Illuminate\\Auth\\Notifications',
        'declaringClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'implementingClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'currentClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'aliasName' => NULL,
      ),
      'createUrlUsing' => 
      array (
        'name' => 'createUrlUsing',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 108,
            'endLine' => 108,
            'startColumn' => 43,
            'endColumn' => 51,
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
 * Set a callback that should be used when creating the reset password button URL.
 *
 * @param  \\Closure(mixed, string): string  $callback
 * @return void
 */',
        'startLine' => 108,
        'endLine' => 111,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Auth\\Notifications',
        'declaringClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'implementingClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'currentClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'aliasName' => NULL,
      ),
      'toMailUsing' => 
      array (
        'name' => 'toMailUsing',
        'parameters' => 
        array (
          'callback' => 
          array (
            'name' => 'callback',
            'default' => NULL,
            'type' => NULL,
            'isVariadic' => false,
            'byRef' => false,
            'isPromoted' => false,
            'attributes' => 
            array (
            ),
            'startLine' => 119,
            'endLine' => 119,
            'startColumn' => 40,
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
        'docComment' => '/**
 * Set a callback that should be used when building the notification mail message.
 *
 * @param  \\Closure(mixed, string): (\\Illuminate\\Notifications\\Messages\\MailMessage|\\Illuminate\\Contracts\\Mail\\Mailable)  $callback
 * @return void
 */',
        'startLine' => 119,
        'endLine' => 122,
        'startColumn' => 5,
        'endColumn' => 5,
        'couldThrow' => false,
        'isClosure' => false,
        'isGenerator' => false,
        'isVariadic' => false,
        'modifiers' => 17,
        'namespace' => 'Illuminate\\Auth\\Notifications',
        'declaringClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'implementingClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
        'currentClassName' => 'Illuminate\\Auth\\Notifications\\ResetPassword',
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