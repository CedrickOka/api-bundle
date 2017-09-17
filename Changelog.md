Changelog
=========

#### 1.6.1 (2017-09-17)

* Added `oka_api.firewalls.wsse.nonce.storage_id` configuration values.
* Added `Oka\ApiBundle\Util\MemcachedWrapper` class.

#### 1.6.0 (2017-09-16)

* Added Nonce Storage Strategy for save WS-Security usernameToken propertie `nonce`.
* Added `oka_api.firewalls.wsse.nonce.save_path` configuration values.
* Added `oka_api.firewalls.wsse.nonce.handler_id` configuration values.
* Allows the extension of the Nonce Storage Strategy by the definition of customs nonce handler.
* Added `FileNonceHandler` class.
* Added `MemcacheNonceHandler` class.
* Added `MemcachedNonceHandler` class.
* Used by default `FileNonceHandler` class for handle nonce.

#### 1.5.0 (2017-09-16)

* [BC break] The signature of the `WsseUserToken` constructor has changed.
* [BC break] The signature of the `WsseListener` constructor has changed.
* Added `Oka\ApiBundle\Security\Authentication\Token\WsseUserToken` properties `$credentials` and associated getter.
* Used `WsseUserToken::setAttribute()` method in `WsseListener` security listener for save the usernameToken properties `digest`, `nonce` and `created`.
* Translated responses sended by `WsseListener` security listener.
* Improve `WsseUserProvider::supportsClass()` method.
* Updated French translation.

#### 1.4.8 (2017-09-14)

* Improved `ErrorResponseFactory::createFromException()`, enabling it to better handle `HttpExceptionInterface` type exceptions.

#### 1.4.7 (2017-09-13)

* [BC break] Send http error code `401` instead of `403` when WSSE authentication fails.
* Added http response header `WWW-Authenticate:` when WSSE authentication fails.

#### 1.4.6 (2017-09-06)

* Used `annotations.cached_reader` service like dependency of `AnnotationListerner` class instead `annotations_reader`.

#### 1.4.5 (2017-09-06)

* Fixed undefined variable `$error` in `AnnotationListerner` class
* Added `validation_error_message` attribute in `@RequestContent` annotation.
* Added `translation` attribute in `@RequestContent` annotation.

#### 1.4.4 (2017-09-05)

* Fixed bad definition method `UserInterface::getPlainPassword($password)`.

#### 1.4.3 (2017-09-05)

* Fixed call undefined method `RequestContent::getName()` in `AnnotationListerner` class.

#### 1.4.2 (2017-09-04)

* Added `UserInterface` class.
* Added `oka_api.util.password_updater` service.

#### 1.4.1 (2017-08-25)

* Triggered deprecated message when the configuration value `oka_api.client_class` is used.

#### 1.4.0 (2017-08-25)

* Added `oka_api.firewalls.wsse.user_class` configuration values.
* Deprecated `oka_api.client_class` configuration values use instead `oka_api.firewalls.wsse.user_class`.
* Improve wsse security authentication.
* Added `WsseUserAllowedIpsVoter` class for user authorization.
* Ability to disable the wsse allowed ips voter with `oka_api.firewalls.wsse.enabled_allowed_ips_voter` configuration values.
* Improved service dependency injection by defining the services of enabled firewalls during the construction process of container.
* Added `WsseUtil` for ws-security genearte username token.
* Fixed changed package name from `Oka\ApiBundle\Service\LoggerHelper` to `Oka\ApiBundle\Util\LoggerHelper` in class use statement.
* Improves class test.
* Improved documentation.

#### 1.3.0 (2017-08-25)

* Added a fluent interface for the entities.
* Moved the role constants to the WsseUserInterface instead of the abstract WsseUser class
* Added `Oka\ApiBundle\Model\WsseUser` properties `$allowedIps` and associated setter and getter.
* Added user manipulator events.
* Added user manipulator service with ID `oka_api.util.wsse_user_manipulator`.
* Added user manipulator commands.
* Moved `LoggerHelper` from package `Oka\ApiBundle\Service\LoggerHelper` to `Oka\ApiBundle\Util\LoggerHelper`.

#### 1.2.4 (2017-08-23)

* Improve bundle documentation.

#### 1.2.3 (2017-08-22)

* Fixed bug in method `ErrorResponseFactory::createFromConstraintViolationList()`.

#### 1.2.2 (2017-08-22)

* Improve `@RequestContent` annotation handling.
* Improve PHPDocs.
* [BC break] Removed `Oka\ApiBundle\Util\ErrorResponseFactory` class, use instead `oka_api.error_response.factory` service.

#### 1.2.1 (2017-08-22)

* Added translations file for french and english.
* Allows to retrieve the parameters of the request in the uri to serve as requestContent.

#### 1.2.0 (2017-07-28)

* [BC break] Renamed `RequestHelper` class in `RequestUtil` class.
* [BC break] Removed `RequestHelper` service.
* [BC break] Removed `ResponseHelper` service.
* [BC break] Removed `RequestParser` class.
* Added new `response.error_builder_class` configuration values.
* Added `ErrorResponseBuilderInterface` class.
* Refactored ErrorResponseFactory like symfony service.
* Added `StringUtil` class.
* Added `RequestUtil` class.

#### 1.1.1 (2017-07-24)

* Added `ErrorResponseFactory::createFromFormErrorIterator()` methods.

#### 1.1.0 (2017-07-21)

* Added `ErrorResponseBuilder` class which creates an instance of the Response object.
* Added `ErrorResponseFactory` class which creates an instance of the Response object.
* Used `ErrorResponseBuilder` and `ErrorResponseFactory` in internal methods.
* [BC break] Removed RequestHelper::parseQueryString() deprecated method.
* [BC break] Removed LoggerHelper::formatErrorMessage() deprecated method.

#### 1.0.0 (2017-07-09)

* Added WS-Security headers authentication.
* Added CORS support request.
* Added the `Access Control` annotation class for content negotiation module.
* Added the `RequestContent` annotation class for input validation module.