Changelog
=========

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