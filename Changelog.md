Changelog
=========

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