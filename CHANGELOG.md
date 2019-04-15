Changelog
=========

#### 4.1.1 (2019-04-15)

* Fixed COSR support bug.

#### 4.1.0 (2019-03-30)

* Deprecated `X-WSSE` header, use instead `Authorization` header.
* Fixed autowire bug.

#### 4.0.0 (2019-03-20)

* [BC break] Removed deprecated property `property` from error response.

#### 3.1.1 (2019-03-20)

* Fixed bug.

#### 3.1.0 (2019-03-20)

* Deprecated `property` in error response and added instead `propertyPath`.

#### 3.0.16 (2019-01-08)

* Fixed bug.

#### 3.0.15 (2018-10-17)

* Fixed bug.

#### 3.0.14 (2018-10-17)

* Fixed bug.

#### 3.0.13 (2018-10-16)

* Deprecated `oka_api.model_manager_name` configuration values.
* Added `oka_api.firewalls.wsse.model_manager_name` configuration values.
* Fixed bug.

#### 3.0.12 (2018-10-16)

* Improves composer definition.
* Fixed bug

#### 3.0.11 (2018-10-16)

* Improves composer definition.

#### 3.0.10 (2018-10-16)

* Fixed bug.

#### 3.0.9 (2018-10-16)

* Fixed bug.

#### 3.0.8 (2018-10-16)

* Improves composer configuration.

#### 3.0.7 (2018-10-15)

* Improves composer configuration.

#### 3.0.6 (2018-10-15)

* Fixed bug.

#### 3.0.5 (2018-10-15)

* Fixed bug.

#### 3.0.4 (2018-10-15)

* Fixed bad composer dependency.

#### 3.0.3 (2018-10-15)

* Fixed bad composer dependency.

#### 3.0.2 (2018-10-15)

* Fixed bad composer dependency.

#### 3.0.1 (2018-10-15)

* Fixed bad composer dependency.

#### 3.0.0 (2018-10-15)

* [BC Break] Updated `composer.json` file and removed Symfony 2.8 support.
* [BC Break] Updated `composer.json` file and added Symfony 4.0 support.

#### 2.2.1 (2018-08-17)

* Fixed an error in `README.md` file.

#### 2.2.0 (2018-08-16)

* Improves `PasswordUpdater::hashPassword()` method.

#### 2.1.1 (2018-08-16)

* Fix bug.

#### 2.1.0 (2018-08-16)

* Deprecated "Oka\ApiBundle\Model\UserPasswordInterface" interface.

#### 2.0.0 (2018-08-16)

* [BC break] Removed deprecated class.
* [BC break] Removed from `Oka\ApiBundle\Model\UserInterface`.
* Added `Oka\ApiBundle\Model\UserPasswordInterface`.
* Added Security behaviors password updater.

#### 1.12.0 (2018-06-14)

* Improves Request CORS support.
* Improves error response handling.

#### 1.11.3 (2018-06-07)

* Improves request `Accept` header handle.

#### 1.11.2 (2018-06-06)

* Fixed `WsseUtil::generateNonce()` method bad implementation.
* Fixed `WsseUtil::generateToken()` method bad implementation.

#### 1.11.1 (2018-05-14)

* Fixed `RequestUtil::getContentLikeArray()` method not return an empty array when `json_decode` function failed.

#### 1.11.0 (2018-05-13)

* Improves html serializer encoder class.
* Added `Oka\ApiBundle\Serializer\Behavior\FieldsExclusionStrategyInterface` class.
* Added `Oka\ApiBundle\Serializer\Behavior\Contextable` class.

#### 1.10.3 (2018-04-18)

* Fixed CORS support bug.

#### 1.10.2 (2018-04-18)

* Changed project folders structure.
* Fixed CORS support bug.

#### 1.10.1 (2018-03-21)

* Marked `oka_api.response.compression.enabled` configuration value has `false` by default.

#### 1.10.0 (2018-03-18)

* Added Response content compression Component.
* Added `oka_api.response.compression` array configuration values.

#### 1.9.0 (2018-01-08)

* Improves errors management for `Symfony\Component\Security\Core\User\AdvancedUserInterface` interface for wsse firewalls.
* Adds support for symfony framework 3.x.x.

#### 1.8.1 (2017-11-15)

* Improved request execption handling.

#### 1.8.0 (2017-11-03)

* Deprecated `oka_api.firewalls.wsse.nonce.storage_id` configuration values.
* Removed `Oka\ApiBundle\Security\Nonce\Storage\NonceStorageInterface` interface.
* Removed `Oka\ApiBundle\Security\Nonce\Storage\NativeNonceStorage` class.
* [BC break] Added `NonceInterface::getIssuedAt()`.
* [BC break] Modified `NonceHandlerInterface::open()` signature.

#### 1.7.0 (2017-10-13)

* Improve Request listener handling.

#### 1.6.6 (2017-09-22)

* Updated composer.json.
* [BC break] Removed `ErrorResponseBuilderInterface::Builder()` deprecated method since bundle version `1.6.3`.
* [BC break] Removed `ErrorResponseBuilder::Builder()` deprecated method since bundle version `1.6.3`.

#### 1.6.6 (2017-09-22)

* Updated composer.json.
* [BC break] Removed `ErrorResponseBuilderInterface::Builder()` deprecated method since bundle version `1.6.3`.
* [BC break] Removed `ErrorResponseBuilder::Builder()` deprecated method since bundle version `1.6.3`.

#### 1.6.5 (2017-09-22)

* Removed custom response header `X-Secure-With`.
* Updated composer.json.

#### 1.6.4 (2017-09-19)

* Fixed text files should end with a newline character.

#### 1.6.3 (2017-09-19)

* Renamed file from `Changelog.mg` to `CHANGELOG.mg`.
* Updated `CHANGELOG`.
* Updated `READMED`.
* Updated `LICENCE`.
* Updated documentation.
* Fixed text files should end with a newline character.
* Deprecated `ErrorResponseBuilderInterface::Builder()` method use instead `ErrorResponseBuilderInterface::getInstance()`.

#### 1.6.2 (2017-09-17)

* Fixed Circular reference detected for service "oka_api.wsse.nonce.storage.native", path: "oka_api.wsse.nonce.storage.native -> oka_api.wsse.nonce.storage.native".

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
