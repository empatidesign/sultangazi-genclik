<?php
return [
	// CurlRequest
	'missingCurl'     => 'CURL must be enabled to use the CURLRequest class.',
	'invalidSSLKey'   => 'Unable to determine SSL key. {0} is an invalid file.',
	'sslCertNotFound' => 'SSL certificate not found in {0}.',
	'curlError'       => '{0} : {1}',

	// IncomingRequest
	'invalidNegotiationType' => '{0} is an invalid bargain type. It should be one of: media, charset, encoding, language.',

	// Message
	'invalidHTTPProtocol' => 'Invalid HTTP protocol version. It must be one of the following: {0}',

	// Negotiate
	'emptySupportedNegotiations' => 'You must specify a set of supported values for all Negotiations.',

	// RedirectResponse
	'invalidRoute' => '{0} invalid route.',

	// Response
	'missingResponseStatus' => 'HTTP Response status code is missing.',
	'invalidStatusCode'     => '{0} invalid HTTP response status code',
	'unknownStatusCode'     => 'Unknown HTTP status code: {0}',

	// URI
	'cannotParseURI'       => 'Could not resolve URI: {0}',
	'segmentOutOfRange'    => 'The requested URI part is out of range: {0}',
	'invalidPort'          => 'Ports must be between 0 and 65535. Given: {0}',
	'malformedQueryString' => 'Query strings must not contain URI parts.',

	// Page Not Found
	'pageNotFound'       => 'Page not found.',
	'emptyController'    => 'Controller not specified.',
	'controllerNotFound' => 'Controller or method not found: {0}::{1}',
	'methodNotFound'     => 'Controller method not found: {0}',

	// CSRF
	'disallowedAction' => 'The requested action is not allowed.',

	// Uploaded file moving
	'alreadyMoved' => 'The uploaded file has already been moved.',
	'invalidFile'  => 'The original file is an invalid file.',
	'moveFailed'   => 'Unable to move file {0} to {1} ({2}).',
];
