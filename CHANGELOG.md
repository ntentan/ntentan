# CHANGELOG

## [Unreleased]
### Added
- Added a new ApplicationBuilder class to make it easy to create an instance of the Application class.
- Added initial unit tests for some essential components within this package.

## v0.9.3 - 2025-09-07
- Fixed a bug in the HTTPAuthentication middleware.

## v0.9.2 - 2025-04-13
- Removed unneeded exceptions from the core into submodules where there are necessary.  

## v0.9.1 - 2025-03-15
## Fixes
 - The header method in the implementation of PHP Message interface was completely implemented.

## v0.9.0 - 2025-01-18
First breakup of the ntentan core for a release:
 - Removed all the MVC components so they would be implemented externally.
 - Using a pipeline of requests and response objects that pass through a series of middleware to handle all application requests.

## v0.8.0 - 2024-07-13
Cleaning up the code before breaking apart the ntentan/core.

## v0.6.0 - 2020-02-25
First version with a CHANGELOG