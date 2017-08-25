Changelog
=========

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
* [BC] Removed `Oka\ApiBundle\Util\ErrorResponseFactory` class, use instead `oka_api.error_response.factory` service.

#### 1.2.1 (2017-08-22)

* Added translations file for french and english.
* Allows to retrieve the parameters of the request in the uri to serve as requestContent.

#### 1.2.0 (2017-07-28)

* [BC] Renamed `RequestHelper` class in `RequestUtil` class.
* [BC] Removed `RequestHelper` service.
* [BC] Removed `ResponseHelper` service.
* [BC] Removed `RequestParser` class.
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
* [BC] Removed RequestHelper::parseQueryString() deprecated method.
* [BC] Removed LoggerHelper::formatErrorMessage() deprecated method.

#### 1.0.0 (2017-07-09)

* Added WS-Security headers authentication.
* Added CORS support request.
* Added the `Access Control` annotation class for content negotiation module.
* Added the `RequestContent` annotation class for input validation module.