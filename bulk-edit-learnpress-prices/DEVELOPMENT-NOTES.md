# Development Notes

## Composer

Composer is not required for version 1.0.0. The planned MVP uses a small WordPress-native file structure with direct guarded includes, so adding an autoloader would introduce more setup than value at this stage.

If the plugin grows to include third-party libraries, a larger test suite, or namespaced service classes, Composer can be introduced in a later version.
