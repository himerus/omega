## `config/optional` Directory

Optional configuration items for an extension (module or theme) are stored in the `config/optional` sub-directory.

These are configuration items that have dependencies that are not explicit dependencies of the extension, so they are only installed if all dependencies are met.
For example, in the scenario that module A defines a dependency which requires module B, but module A is installed first and module B some time later, then module A's config/optional directory will be scanned at that time for newly met dependencies, and the configuration will be installed then. 
If module B is never installed, the configuration item will not be installed either.

### Resources
* [Configuration Storage in Drupal 8](https://www.drupal.org/node/2120571)
